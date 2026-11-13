<?php

namespace App\Enums;

enum UserRole: string
{
    case Client = 'client';
    case Counsellor = 'counsellor';
    case Administrator = 'administrator';

    public function label(): string
    {
        return match ($this) {
            self::Client => 'Client',
            self::Counsellor => 'Counsellor',
            self::Administrator => 'Administrator',
        };
    }
}
