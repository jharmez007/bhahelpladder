<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Enums\SessionMode;
use App\Models\Appointment;
use App\Models\CounsellorProfile;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Appointment> */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $start = CarbonImmutable::now('UTC')->addDays(2)->startOfHour();
        $end = $start->addHour();

        return [
            'reference' => (string) Str::uuid(),
            'client_id' => User::factory()->client(),
            'counsellor_profile_id' => CounsellorProfile::factory(),
            'service_id' => Service::factory(),
            'status' => AppointmentStatus::Pending,
            'session_mode' => SessionMode::InPerson,
            'starts_at_utc' => $start,
            'ends_at_utc' => $end,
            'buffer_starts_at_utc' => $start->subMinutes(10),
            'buffer_ends_at_utc' => $end->addMinutes(10),
            'duration_minutes' => 60,
            'buffer_before_minutes' => 10,
            'buffer_after_minutes' => 10,
            'fee_minor' => null,
            'currency' => 'NGN',
        ];
    }
}
