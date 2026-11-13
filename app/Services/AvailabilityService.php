<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Exceptions\BookingConflictException;
use App\Models\Appointment;
use App\Models\CounsellorProfile;
use App\Models\Service;
use Carbon\CarbonImmutable;

class AvailabilityService
{
    /**
     * @return list<array{value: string, label: string}>
     */
    public function availableSlots(
        Service $service,
        CounsellorProfile $profile,
        CarbonImmutable $localDate,
        ?Appointment $ignore = null,
    ): array {
        $timezone = config('booking.timezone');
        $date = $localDate->setTimezone($timezone)->startOfDay();
        $interval = config('booking.slot_interval_minutes');
        $slots = [];

        $rules = $profile->availabilityRules()
            ->where('weekday', $date->dayOfWeek)
            ->orderBy('starts_at')
            ->get();

        foreach ($rules as $rule) {
            $cursor = CarbonImmutable::parse($date->toDateString().' '.$rule->starts_at, $timezone);
            $ruleEnd = CarbonImmutable::parse($date->toDateString().' '.$rule->ends_at, $timezone);

            while ($cursor->addMinutes($service->duration_minutes + $service->buffer_after_minutes)->lte($ruleEnd)) {
                $utc = $cursor->utc();

                try {
                    $this->assertAvailable($service, $profile, $utc, $ignore);
                    $slots[] = [
                        'value' => $utc->toIso8601String(),
                        'label' => $cursor->format('g:i a'),
                    ];
                } catch (BookingConflictException) {
                    // The slot is intentionally omitted.
                }

                $cursor = $cursor->addMinutes($interval);
            }
        }

        return $slots;
    }

    public function assertAvailable(
        Service $service,
        CounsellorProfile $profile,
        CarbonImmutable $startsAtUtc,
        ?Appointment $ignore = null,
    ): void {
        $startsAtUtc = $startsAtUtc->utc();
        $endsAtUtc = $startsAtUtc->addMinutes($service->duration_minutes);
        $bufferStartsAtUtc = $startsAtUtc->subMinutes($service->buffer_before_minutes);
        $bufferEndsAtUtc = $endsAtUtc->addMinutes($service->buffer_after_minutes);
        $now = CarbonImmutable::now('UTC');

        if (! $profile->is_active) {
            throw new BookingConflictException('This counsellor is not currently available for booking.');
        }

        if ($startsAtUtc->lt($now->addHours(config('booking.minimum_notice_hours')))) {
            throw new BookingConflictException('This time does not meet the minimum booking notice.');
        }

        if ($startsAtUtc->gt($now->addDays(config('booking.booking_horizon_days')))) {
            throw new BookingConflictException('This time is outside the booking window.');
        }

        $timezone = config('booking.timezone');
        $localBufferStart = $bufferStartsAtUtc->setTimezone($timezone);
        $localBufferEnd = $bufferEndsAtUtc->setTimezone($timezone);

        if (! $localBufferStart->isSameDay($localBufferEnd)) {
            throw new BookingConflictException('The session and its buffers must fit within one working day.');
        }

        $withinWorkingHours = $profile->availabilityRules()
            ->where('weekday', $localBufferStart->dayOfWeek)
            ->where('starts_at', '<=', $localBufferStart->format('H:i:s'))
            ->where('ends_at', '>=', $localBufferEnd->format('H:i:s'))
            ->exists();

        if (! $withinWorkingHours) {
            throw new BookingConflictException('This time is outside the counsellor’s working hours.');
        }

        $overlapsBreak = $profile->availabilityBreaks()
            ->where('weekday', $localBufferStart->dayOfWeek)
            ->where('starts_at', '<', $localBufferEnd->format('H:i:s'))
            ->where('ends_at', '>', $localBufferStart->format('H:i:s'))
            ->exists();

        if ($overlapsBreak) {
            throw new BookingConflictException('This time overlaps a counsellor break.');
        }

        $isBlocked = $profile->blockedPeriods()
            ->where('starts_at_utc', '<', $bufferEndsAtUtc)
            ->where('ends_at_utc', '>', $bufferStartsAtUtc)
            ->exists();

        if ($isBlocked) {
            throw new BookingConflictException('This time has been blocked by the counsellor.');
        }

        $reservingStatuses = collect(AppointmentStatus::cases())
            ->filter(fn (AppointmentStatus $status) => $status->reservesAvailability())
            ->map->value
            ->all();

        $overlapQuery = $profile->appointments()
            ->whereIn('status', $reservingStatuses)
            ->where('buffer_starts_at_utc', '<', $bufferEndsAtUtc)
            ->where('buffer_ends_at_utc', '>', $bufferStartsAtUtc);

        if ($ignore) {
            $overlapQuery->where('appointments.id', '!=', $ignore->getKey());
        }

        if ($overlapQuery->exists()) {
            throw new BookingConflictException('This time is no longer available. Please choose another slot.');
        }
    }
}
