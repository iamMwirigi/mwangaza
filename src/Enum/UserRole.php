<?php

namespace App\Enum;

enum UserRole: string
{
    case SUPERUSER = 'ROLE_SUPERUSER';
    case ADMINISTRATOR = 'ROLE_ADMIN';
    case MANAGEMENT = 'ROLE_MANAGEMENT';
    case SALES_STAFF = 'ROLE_SALES';
    case CLIENT = 'ROLE_CLIENT';

    public function label(): string
    {
        return match($this) {
            self::SUPERUSER => 'Superuser',
            self::ADMINISTRATOR => 'Administrator',
            self::MANAGEMENT => 'Management (PII)',
            self::SALES_STAFF => 'Sales Staff',
            self::CLIENT => 'Client',
        };
    }
}
