<?php

namespace Database\Factories;

use App\Models\AvailabilityRule;
use App\Models\CounsellorProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AvailabilityRule> */
class AvailabilityRuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'counsellor_profile_id' => CounsellorProfile::factory(),
            'weekday' => 1,
            'starts_at' => '09:00:00',
            'ends_at' => '17:00:00',
        ];
    }
}
