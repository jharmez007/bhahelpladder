<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Enquiry;
use App\Models\User;

class EnquiryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Administrator;
    }

    public function view(User $user, Enquiry $enquiry): bool
    {
        return $this->viewAny($user);
    }
}
