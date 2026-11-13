<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\SessionMode;
use App\Enums\UserRole;
use App\Exceptions\BookingConflictException;
use App\Models\Appointment;
use App\Models\CounsellorProfile;
use App\Models\Service;
use App\Models\User;
use App\Notifications\AppointmentRequestedNotification;
use App\Notifications\AppointmentRescheduledNotification;
use App\Notifications\AppointmentStatusChangedNotification;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(private AvailabilityService $availability) {}

    public function createForClient(
        User $client,
        Service $service,
        CounsellorProfile $profile,
        SessionMode $mode,
        CarbonImmutable $startsAtUtc,
    ): Appointment {
        if ($client->role !== UserRole::Client) {
            throw new AuthorizationException('Only clients can create appointments.');
        }

        $appointment = DB::transaction(function () use ($client, $service, $profile, $mode, $startsAtUtc): Appointment {
            $profile = CounsellorProfile::query()->lockForUpdate()->findOrFail($profile->id);
            $service = Service::query()->lockForUpdate()->findOrFail($service->id);

            $this->assertServiceConfiguration($service, $profile, $mode);
            $this->availability->assertAvailable($service, $profile, $startsAtUtc);

            return $this->persistAppointment($client, $service, $profile, $mode, $startsAtUtc);
        }, 3);

        $client->notify(new AppointmentRequestedNotification($appointment));
        $appointment->counsellorProfile->user->notify(new AppointmentRequestedNotification($appointment));

        return $appointment;
    }

    public function reschedule(
        User $actor,
        Appointment $appointment,
        CarbonImmutable $newStartUtc,
        ?CounsellorProfile $newProfile = null,
    ): Appointment {
        $appointment = DB::transaction(function () use ($actor, $appointment, $newStartUtc, $newProfile): Appointment {
            $appointment = Appointment::query()->lockForUpdate()->findOrFail($appointment->id);
            $this->authorizeReschedule($actor, $appointment);

            if (! $appointment->status->reservesAvailability()) {
                throw new BookingConflictException('Only pending or confirmed appointments can be rescheduled.');
            }

            if ($actor->role === UserRole::Client
                && CarbonImmutable::now('UTC')->addHours(config('booking.reschedule_notice_hours'))->gt($appointment->starts_at_utc)) {
                throw new BookingConflictException('The rescheduling deadline for this appointment has passed.');
            }

            $targetProfileId = $newProfile?->id ?? $appointment->counsellor_profile_id;
            $this->lockProfiles([$appointment->counsellor_profile_id, $targetProfileId]);

            $targetProfile = CounsellorProfile::query()->findOrFail($targetProfileId);
            $service = Service::query()->findOrFail($appointment->service_id);
            $this->assertServiceConfiguration($service, $targetProfile, $appointment->session_mode);

            // Validate before mutating so a rejected reschedule leaves the original intact.
            $this->availability->assertAvailable($service, $targetProfile, $newStartUtc, $appointment);

            $this->setScheduleSnapshot($appointment, $service, $targetProfile, $newStartUtc);
            $appointment->rescheduled_at = now('UTC');
            $appointment->save();

            return $appointment->fresh(['client', 'counsellorProfile.user', 'service']);
        }, 3);

        $appointment->client->notify(new AppointmentRescheduledNotification($appointment));
        $appointment->counsellorProfile->user->notify(new AppointmentRescheduledNotification($appointment));

        return $appointment;
    }

    public function cancel(User $actor, Appointment $appointment): Appointment
    {
        $appointment = DB::transaction(function () use ($actor, $appointment): Appointment {
            $appointment = Appointment::query()->lockForUpdate()->findOrFail($appointment->id);

            if (! $this->canManageAppointment($actor, $appointment)) {
                throw new AuthorizationException('You cannot cancel this appointment.');
            }

            if (! $appointment->status->canTransitionTo(AppointmentStatus::Cancelled)) {
                throw new BookingConflictException('This appointment can no longer be cancelled.');
            }

            if ($actor->role === UserRole::Client
                && CarbonImmutable::now('UTC')->addHours(config('booking.cancellation_notice_hours'))->gt($appointment->starts_at_utc)) {
                throw new BookingConflictException('The cancellation deadline for this appointment has passed.');
            }

            $appointment->status = AppointmentStatus::Cancelled;
            $appointment->cancelled_at = now('UTC');
            $appointment->cancelled_by = $actor->id;
            $appointment->save();

            return $appointment->fresh(['client', 'counsellorProfile.user', 'service']);
        }, 3);

        $this->sendStatusNotifications($appointment);

        return $appointment;
    }

    public function transition(User $actor, Appointment $appointment, AppointmentStatus $target): Appointment
    {
        if ($target === AppointmentStatus::Cancelled) {
            return $this->cancel($actor, $appointment);
        }

        $appointment = DB::transaction(function () use ($actor, $appointment, $target): Appointment {
            $appointment = Appointment::query()->lockForUpdate()->findOrFail($appointment->id);

            if (! $this->isStaffForAppointment($actor, $appointment)) {
                throw new AuthorizationException('You cannot update this appointment.');
            }

            if (! $appointment->status->canTransitionTo($target)) {
                throw new BookingConflictException('That appointment status transition is not allowed.');
            }

            $appointment->status = $target;
            $appointment->save();

            return $appointment->fresh(['client', 'counsellorProfile.user', 'service']);
        }, 3);

        $this->sendStatusNotifications($appointment);

        return $appointment;
    }

    public function assignCounsellor(User $administrator, Appointment $appointment, CounsellorProfile $newProfile): Appointment
    {
        if ($administrator->role !== UserRole::Administrator) {
            throw new AuthorizationException('Only administrators can reassign appointments.');
        }

        return $this->reschedule(
            $administrator,
            $appointment,
            $appointment->starts_at_utc,
            $newProfile,
        );
    }

    public function updateMeetingDetails(User $actor, Appointment $appointment, ?string $details): Appointment
    {
        $appointment = Appointment::query()->findOrFail($appointment->id);

        if (! $this->isStaffForAppointment($actor, $appointment)) {
            throw new AuthorizationException('You cannot update meeting details.');
        }

        if ($appointment->session_mode !== SessionMode::Online) {
            throw new BookingConflictException('Meeting details apply only to online appointments.');
        }

        $appointment->online_meeting_details = filled($details) ? trim($details) : null;
        $appointment->save();

        return $appointment;
    }

    private function persistAppointment(
        User $client,
        Service $service,
        CounsellorProfile $profile,
        SessionMode $mode,
        CarbonImmutable $startsAtUtc,
    ): Appointment {
        $appointment = new Appointment;
        $appointment->client_id = $client->id;
        $appointment->service_id = $service->id;
        $appointment->status = AppointmentStatus::Pending;
        $appointment->session_mode = $mode;
        $this->setScheduleSnapshot($appointment, $service, $profile, $startsAtUtc);
        $appointment->save();

        return $appointment->fresh(['client', 'counsellorProfile.user', 'service']);
    }

    private function setScheduleSnapshot(
        Appointment $appointment,
        Service $service,
        CounsellorProfile $profile,
        CarbonImmutable $startsAtUtc,
    ): void {
        $startsAtUtc = $startsAtUtc->utc();
        $endsAtUtc = $startsAtUtc->addMinutes($service->duration_minutes);

        $appointment->counsellor_profile_id = $profile->id;
        $appointment->starts_at_utc = $startsAtUtc;
        $appointment->ends_at_utc = $endsAtUtc;
        $appointment->buffer_starts_at_utc = $startsAtUtc->subMinutes($service->buffer_before_minutes);
        $appointment->buffer_ends_at_utc = $endsAtUtc->addMinutes($service->buffer_after_minutes);
        $appointment->duration_minutes = $service->duration_minutes;
        $appointment->buffer_before_minutes = $service->buffer_before_minutes;
        $appointment->buffer_after_minutes = $service->buffer_after_minutes;
        $appointment->fee_minor = $service->fee_minor;
        $appointment->currency = $service->currency;
    }

    private function assertServiceConfiguration(Service $service, CounsellorProfile $profile, SessionMode $mode): void
    {
        if (! $service->is_active) {
            throw new BookingConflictException('This service is not currently bookable.');
        }

        if (! in_array($mode->value, $service->modes ?? [], true)) {
            throw new BookingConflictException('The selected session mode is not available for this service.');
        }

        if (! $profile->services()->whereKey($service->id)->exists()) {
            throw new BookingConflictException('The selected counsellor is not assigned to this service.');
        }
    }

    /** @param list<int> $profileIds */
    private function lockProfiles(array $profileIds): void
    {
        CounsellorProfile::query()
            ->whereKey(array_values(array_unique($profileIds)))
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }

    private function authorizeReschedule(User $actor, Appointment $appointment): void
    {
        if ($actor->role === UserRole::Client && $appointment->client_id !== $actor->id) {
            throw new AuthorizationException('You cannot reschedule this appointment.');
        }

        if (! $this->canManageAppointment($actor, $appointment)) {
            throw new AuthorizationException('You cannot reschedule this appointment.');
        }
    }

    private function canManageAppointment(User $actor, Appointment $appointment): bool
    {
        return match ($actor->role) {
            UserRole::Administrator => true,
            UserRole::Client => $appointment->client_id === $actor->id,
            UserRole::Counsellor => $appointment->counsellorProfile()->where('user_id', $actor->id)->exists(),
        };
    }

    private function isStaffForAppointment(User $actor, Appointment $appointment): bool
    {
        return $actor->role === UserRole::Administrator
            || ($actor->role === UserRole::Counsellor
                && $appointment->counsellorProfile()->where('user_id', $actor->id)->exists());
    }

    private function sendStatusNotifications(Appointment $appointment): void
    {
        $appointment->client->notify(new AppointmentStatusChangedNotification($appointment));
        $appointment->counsellorProfile->user->notify(new AppointmentStatusChangedNotification($appointment));
    }
}
