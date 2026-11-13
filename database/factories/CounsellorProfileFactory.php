<?php

namespace Database\Factories;

use App\Models\CounsellorProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CounsellorProfile> */
class CounsellorProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->counsellor(),
            'is_active' => true,
        ];
    }
}
