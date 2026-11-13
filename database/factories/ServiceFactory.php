<?php

namespace Database\Factories;

use App\Enums\SessionMode;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Service> */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'description' => fake()->sentence(),
            'duration_minutes' => 60,
            'buffer_before_minutes' => 10,
            'buffer_after_minutes' => 10,
            'fee_minor' => null,
            'currency' => 'NGN',
            'modes' => [SessionMode::InPerson->value, SessionMode::Online->value],
            'is_active' => true,
        ];
    }
}
