<?php

namespace App\Support;

use App\Enums\UserRole;

class RoleRedirectMap
{
    public static function getMap(): array
    {
        return [
            UserRole::SuperAdmin->value => '/coop',
            UserRole::Admin->value      => '/coop',
            UserRole::Librarian->value  => '/library-dashboard',
            UserRole::Employee->value   => '/employee-dashboard',
            UserRole::Member->value     => '/coop/savings-accounts',
        ];
    }

    public static function getRedirect(?string $role): string
    {
        if (! $role) {
            return '/';
        }

        return self::getMap()[$role] ?? '/';
    }
}
