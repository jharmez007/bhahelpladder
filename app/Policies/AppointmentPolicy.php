<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function view(User $user, Appointment $appointment): bool
    {
        return match ($user->role) {
            UserRole::Administrator => true,
            UserRole::Client => $appointment->client_id === $user->id,
            UserRole::Counsellor => $appointment->counsellorProfile()->where('user_id', $user->id)->exists(),
        };
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Client;
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $this->view($user, $appointment);
    }
}
