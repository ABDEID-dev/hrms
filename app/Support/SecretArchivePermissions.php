<?php

namespace App\Support;

use App\Models\User;

class SecretArchivePermissions
{
    public const ACCOUNTS = ['maktoom', 'avani'];

    public static function isSuperAdmin(?User $user): bool
    {
        return (int) $user?->getKey() === 1;
    }

    public static function canView(?User $user, string $account): bool
    {
        if (! $user || ! in_array($account, self::ACCOUNTS, true)) {
            return false;
        }

        if (self::isSuperAdmin($user)) {
            return true;
        }

        return $user->canAccessAccountBranch($account)
            && $user->can('view accounts '.$account)
            && ($user->can('view secret archive '.$account)
                || $user->can('manage secret archive '.$account));
    }

    public static function canManage(?User $user, string $account): bool
    {
        return self::isSuperAdmin($user)
            || (self::canView($user, $account) && $user->can('manage secret archive '.$account));
    }

    public static function accessibleAccounts(?User $user): array
    {
        return collect(self::ACCOUNTS)
            ->filter(fn (string $account): bool => self::canView($user, $account))
            ->values()
            ->all();
    }
}
