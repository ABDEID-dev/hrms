<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MenuPermissions
{
    private static array $slugPermissions = [
        'dashboard' => ['view dashboard'],
        'employee-portal' => ['view employee portal'],
        'employee-management-responses' => ['view employee management responses'],
        'employee-complaints' => ['view employee complaints'],
        'attendance-fingerprints' => ['view attendance fingerprints'],
        'employee-tracking' => ['view attendance fingerprints'],
        'admin-tracking' => ['view attendance fingerprints'],
        'attendance-leaves' => ['view attendance leaves'],
        'structure-centers' => ['view structure centers'],
        'structure-departments' => ['view structure departments'],
        'structure-positions' => ['view structure positions'],
        'structure-employees' => ['view employees'],
        'structure-employee-documents' => ['view employee documents'],
        'messages-bulk' => ['view messages bulk'],
        'messages-personal' => ['view messages personal'],
        'messages-employee-requests' => ['view employee requests'],
        'management-complaints' => ['manage management complaints'],
        'messages-deleted-documents' => ['view deleted documents'],
        'discounts' => ['view discounts'],
        'salary-report' => ['view salary report'],
        'customers' => ['view customers'],
        'accounts-employee-revenues' => ['view employee revenues'],
        'accounts-treasury-audit' => ['view treasury audit'],
        'accounts-expense-reports' => ['view accounts treasury'],
        'accounts-payroll-payments' => ['view accounts treasury'],
        'accounts-maktoom-revenues' => ['view accounts maktoom', 'manage accounts revenues'],
        'accounts-maktoom-expenses' => ['view accounts maktoom', 'manage accounts expenses'],
        'accounts-maktoom-treasury' => ['view accounts maktoom', 'view accounts treasury'],
        'accounts-maktoom-monthly-income-report' => ['view accounts maktoom', 'view accounts treasury'],
        'accounts-avani-revenues' => ['view accounts avani', 'manage accounts revenues'],
        'accounts-avani-expenses' => ['view accounts avani', 'manage accounts expenses'],
        'accounts-avani-treasury' => ['view accounts avani', 'view accounts treasury'],
        'accounts-avani-monthly-income-report' => ['view accounts avani', 'view accounts treasury'],
        'accounts-perfumes-revenues' => ['view accounts perfumes', 'manage accounts revenues'],
        'accounts-perfumes-expenses' => ['view accounts perfumes', 'manage accounts expenses'],
        'accounts-perfumes-treasury' => ['view accounts perfumes', 'view accounts treasury'],
        'accounts-perfumes-monthly-income-report' => ['view accounts perfumes', 'view accounts treasury'],
        'holidays' => ['view holidays'],
        'statistics' => ['view statistics'],
        'settings-users' => ['view settings users'],
        'settings-roles' => ['view settings roles', 'manage permissions'],
        'settings-permissions' => ['view settings permissions', 'manage permissions'],
        'settings-logs' => ['view logs'],
        'products' => ['view assets inventory'],
        'maktoom-dyes' => ['view maktoom dyes'],
        'maktoom-dye-revenues' => ['view maktoom dyes'],
        'maktoom-dye-reports' => ['view maktoom dyes'],
        'categories' => ['view assets categories'],
        'reports' => ['view assets reports'],
        'salon-services' => ['view salon services'],
        'salon-invoices' => ['view salon invoices'],
    ];

    private static array $headerPermissions = [
        'Human resource' => [
            'view attendance fingerprints',
            'view attendance leaves',
            'view structure centers',
            'view structure departments',
            'view structure positions',
            'view employees',
            'view employee documents',
            'view messages bulk',
            'view messages personal',
            'view employee requests',
            'manage management complaints',
            'view deleted documents',
            'view discounts',
            'view holidays',
            'view statistics',
        ],
        'ادارة الاعمال' => [
            'view salary report',
            'view employee revenues',
            'view treasury audit',
            'view accounts treasury',
        ],
        'الحسابات' => [
            'view accounts maktoom',
            'view accounts avani',
            'view accounts perfumes',
            'manage accounts revenues',
            'manage accounts expenses',
            'view accounts treasury',
        ],
        'Ø§Ù„Ø­Ø³Ø§Ø¨Ø§Øª' => [
            'view accounts maktoom',
            'view accounts avani',
            'view accounts perfumes',
            'manage accounts revenues',
            'manage accounts expenses',
            'view accounts treasury',
        ],
        'Assets' => [
            'view assets inventory',
            'manage assets inventory',
            'view assets categories',
            'view assets reports',
            'view salon services',
            'view salon invoices',
            'view maktoom dyes',
        ],
    ];

    private static array $revenueActionPermissions = [
        'manage accounts revenues',
        'create account revenues',
        'edit account revenues',
        'delete account revenues',
        'create backdated account revenues',
        'edit backdated account revenues',
        'delete backdated account revenues',
    ];

    private static array $expenseActionPermissions = [
        'manage accounts expenses',
        'create account expenses',
        'create backdated account expenses',
        'edit account expenses',
        'delete account expenses',
        'edit backdated account expenses',
        'delete backdated account expenses',
    ];

    public static function canSee(object $item): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if (isset($item->menuHeader)) {
            return self::canAny(self::$headerPermissions[$item->menuHeader] ?? [])
                || ($item->menuHeader === 'Assets' && self::canSeeAccountScopedInventory());
        }

        $permissions = self::permissionsFor($item);
        $slug = $item->slug ?? null;

        if ($slug === 'accounts-treasury-audit') {
            return $user->hasRole('Admin');
        }

        if ($slug === 'accounts-payroll-payments') {
            return $user->hasAnyRole(['Admin', 'ManagementEmployee']);
        }

        if (is_string($slug) && preg_match('/^accounts-(maktoom|avani|perfumes)-revenues$/', $slug, $matches)) {
            return self::canAccessAccountPage($matches[1], self::$revenueActionPermissions);
        }

        if (is_string($slug) && preg_match('/^accounts-(maktoom|avani|perfumes)-expenses$/', $slug, $matches)) {
            return self::canAccessAccountPage($matches[1], self::$expenseActionPermissions);
        }

        if ($slug === 'employee-tracking') {
            return $user->hasRole('Admin');
        }

        if ($slug === 'admin-tracking') {
            return (int) $user->id === 1;
        }

        if ($slug === 'secret-archive') {
            return SecretArchivePermissions::accessibleAccounts($user) !== [];
        }

        if ($slug === 'products') {
            return self::canSeeAccountScopedInventory();
        }

        if (in_array($slug, ['maktoom-dyes', 'maktoom-dye-revenues', 'maktoom-dye-reports'], true)) {
            return $user->canAccessAccountBranch('maktoom') && $user->can('view maktoom dyes');
        }

        if (isset($item->submenu)) {
            return self::canSeeAnyChild((array) $item->submenu)
                || ($permissions !== [] && self::canAll($permissions));
        }

        return $permissions === [] || self::canAll($permissions);
    }

    private static function canSeeAnyChild(array $children): bool
    {
        foreach ($children as $child) {
            if (is_object($child) && self::canSee($child)) {
                return true;
            }
        }

        return false;
    }

    private static function permissionsFor(object $item): array
    {
        if (isset($item->permission)) {
            return (array) $item->permission;
        }

        $slug = $item->slug ?? null;

        if (is_array($slug)) {
            return collect($slug)
                ->flatMap(fn ($value) => self::$slugPermissions[$value] ?? [])
                ->unique()
                ->values()
                ->all();
        }

        return is_string($slug) ? (self::$slugPermissions[$slug] ?? []) : [];
    }

    private static function canAll(array $permissions): bool
    {
        $user = Auth::user();

        foreach ($permissions as $permission) {
            if (preg_match('/^(?:view|manage) accounts (maktoom|avani|perfumes)$/', $permission, $matches)
                && ! $user?->canAccessAccountBranch($matches[1])) {
                return false;
            }

            if (! $user?->can($permission)) {
                return false;
            }
        }

        return true;
    }

    private static function canAny(array $permissions): bool
    {
        $user = Auth::user();

        if ($permissions === []) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (preg_match('/^(?:view|manage) accounts (maktoom|avani|perfumes)$/', $permission, $matches)
                && ! $user?->canAccessAccountBranch($matches[1])) {
                continue;
            }

            if ($user?->can($permission)) {
                return true;
            }
        }

        return false;
    }

    private static function canSeeAccountScopedInventory(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        foreach (array_keys(User::accountBranches()) as $account) {
            if ($user->canAccessAccountBranch($account)
                && ($user->can('view assets inventory') || $user->can('view accounts '.$account))) {
                return true;
            }
        }

        return false;
    }

    private static function canAccessAccountPage(string $account, array $actionPermissions): bool
    {
        $user = Auth::user();

        return (bool) $user
            && $user->canAccessAccountBranch($account)
            && $user->can('view accounts '.$account)
            && self::canAny($actionPermissions);
    }
}
