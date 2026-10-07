<?php

namespace App\Support;

use App\Models\AccountTransaction;
use App\Models\User;
use Carbon\Carbon;

class AccountPermissions
{
    private const BUSINESS_TIMEZONE = 'Asia/Dubai';

    public static function canView(User $user, string $account): bool
    {
        return $user->canAccessAccountBranch($account)
            && $user->can('view accounts '.$account);
    }

    public static function canCreateRevenue(User $user, string $account): bool
    {
        return self::canUseAnyAccountPermission($user, $account, [
            'manage accounts revenues',
            'create account revenues',
            'create backdated account revenues',
        ]);
    }

    public static function canEditRevenue(User $user, string $account, AccountTransaction $revenue, string $currentBusinessDate, bool $businessWindowOpen): bool
    {
        return self::canManageDatedAccountRecord(
            $user,
            $account,
            $revenue,
            $currentBusinessDate,
            $businessWindowOpen,
            'edit account revenues',
            'edit backdated account revenues',
            'manage accounts revenues',
        );
    }

    public static function canDeleteRevenue(User $user, string $account, AccountTransaction $revenue, string $currentBusinessDate, bool $businessWindowOpen): bool
    {
        return self::canManageDatedAccountRecord(
            $user,
            $account,
            $revenue,
            $currentBusinessDate,
            $businessWindowOpen,
            'delete account revenues',
            'delete backdated account revenues',
            'manage accounts revenues',
        );
    }

    public static function canCreateBackdatedRevenue(User $user, string $account): bool
    {
        return self::canUseBackdatedPermission($user, $account, 'create backdated account revenues');
    }

    public static function canEditRevenueDateTime(User $user, string $account): bool
    {
        return self::canUseBackdatedPermission($user, $account, 'edit backdated account revenues');
    }

    public static function canCreateExpense(User $user, string $account): bool
    {
        return self::canUseAnyAccountPermission($user, $account, [
            'manage accounts expenses',
            'create account expenses',
            'create backdated account expenses',
        ]);
    }

    public static function canCreateBackdatedExpense(User $user, string $account): bool
    {
        return self::canUseBackdatedPermission($user, $account, 'create backdated account expenses');
    }

    public static function canEditExpense(User $user, string $account, AccountTransaction $expense, string $currentBusinessDate, bool $businessWindowOpen): bool
    {
        return self::canManageDatedAccountRecord(
            $user,
            $account,
            $expense,
            $currentBusinessDate,
            $businessWindowOpen,
            'edit account expenses',
            'edit backdated account expenses',
            'manage accounts expenses',
        );
    }

    public static function canDeleteExpense(User $user, string $account, AccountTransaction $expense, string $currentBusinessDate, bool $businessWindowOpen): bool
    {
        return self::canManageDatedAccountRecord(
            $user,
            $account,
            $expense,
            $currentBusinessDate,
            $businessWindowOpen,
            'delete account expenses',
            'delete backdated account expenses',
            'manage accounts expenses',
        );
    }

    public static function canEditExpenseDateTime(User $user, string $account): bool
    {
        return self::canUseBackdatedPermission($user, $account, 'edit backdated account expenses');
    }

    private static function canManageDatedAccountRecord(
        User $user,
        string $account,
        AccountTransaction $transaction,
        string $currentBusinessDate,
        bool $businessWindowOpen,
        string $currentDatePermission,
        string $backdatedPermission,
        string $managePermission,
    ): bool {
        if (! self::canView($user, $account)) {
            return false;
        }

        if ($user->hasRole('Admin')) {
            return true;
        }

        $transactionDate = Carbon::parse($transaction->date, self::BUSINESS_TIMEZONE)->toDateString();

        if ($transactionDate > $currentBusinessDate) {
            return false;
        }

        if ($transactionDate < $currentBusinessDate) {
            return $user->can($backdatedPermission);
        }

        return $user->can($backdatedPermission)
            || $user->can($managePermission)
            || ($businessWindowOpen && $user->can($currentDatePermission));
    }

    private static function canUseBackdatedPermission(User $user, string $account, string $permission): bool
    {
        return self::canView($user, $account)
            && ($user->hasRole('Admin') || $user->can($permission));
    }

    private static function canUseAnyAccountPermission(User $user, string $account, array $permissions): bool
    {
        if (! self::canView($user, $account)) {
            return false;
        }

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }
}
