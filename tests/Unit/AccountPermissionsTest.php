<?php

namespace Tests\Unit;

use App\Models\AccountTransaction;
use App\Models\User;
use App\Support\AccountPermissions;
use Mockery;
use Tests\TestCase;

class AccountPermissionsTest extends TestCase
{
    public function test_general_revenue_management_does_not_allow_historical_edits_or_deletes(): void
    {
        $user = $this->permissionUser(['manage accounts revenues']);
        $revenue = new AccountTransaction(['date' => '2026-09-26']);

        $this->assertFalse(AccountPermissions::canEditRevenue($user, 'maktoom', $revenue, '2026-09-27', true));
        $this->assertFalse(AccountPermissions::canDeleteRevenue($user, 'maktoom', $revenue, '2026-09-27', true));
        $this->assertFalse(AccountPermissions::canEditRevenueDateTime($user, 'maktoom'));
        $this->assertFalse(AccountPermissions::canCreateBackdatedRevenue($user, 'maktoom'));
    }

    public function test_historical_revenue_actions_require_the_matching_explicit_permission(): void
    {
        $revenue = new AccountTransaction(['date' => '2026-09-26']);
        $editUser = $this->permissionUser(['edit backdated account revenues']);
        $deleteUser = $this->permissionUser(['delete backdated account revenues']);
        $futureRevenue = new AccountTransaction(['date' => '2026-09-28']);

        $this->assertTrue(AccountPermissions::canEditRevenue($editUser, 'maktoom', $revenue, '2026-09-27', false));
        $this->assertFalse(AccountPermissions::canDeleteRevenue($editUser, 'maktoom', $revenue, '2026-09-27', false));
        $this->assertTrue(AccountPermissions::canEditRevenueDateTime($editUser, 'maktoom'));
        $this->assertTrue(AccountPermissions::canDeleteRevenue($deleteUser, 'maktoom', $revenue, '2026-09-27', false));
        $this->assertFalse(AccountPermissions::canEditRevenue($deleteUser, 'maktoom', $revenue, '2026-09-27', false));
        $this->assertFalse(AccountPermissions::canEditRevenue($editUser, 'maktoom', $futureRevenue, '2026-09-27', false));
    }

    public function test_general_revenue_management_still_allows_current_day_actions(): void
    {
        $user = $this->permissionUser(['manage accounts revenues']);
        $revenue = new AccountTransaction(['date' => '2026-09-27']);

        $this->assertTrue(AccountPermissions::canEditRevenue($user, 'maktoom', $revenue, '2026-09-27', false));
        $this->assertTrue(AccountPermissions::canDeleteRevenue($user, 'maktoom', $revenue, '2026-09-27', false));
    }

    public function test_historical_expense_actions_require_the_matching_explicit_permission(): void
    {
        $expense = new AccountTransaction(['date' => '2026-09-26']);
        $manager = $this->permissionUser(['manage accounts expenses']);
        $editUser = $this->permissionUser(['edit backdated account expenses']);
        $deleteUser = $this->permissionUser(['delete backdated account expenses']);

        $this->assertFalse(AccountPermissions::canEditExpense($manager, 'maktoom', $expense, '2026-09-27', true));
        $this->assertFalse(AccountPermissions::canDeleteExpense($manager, 'maktoom', $expense, '2026-09-27', true));
        $this->assertFalse(AccountPermissions::canEditExpenseDateTime($manager, 'maktoom'));
        $this->assertTrue(AccountPermissions::canEditExpense($editUser, 'maktoom', $expense, '2026-09-27', false));
        $this->assertFalse(AccountPermissions::canDeleteExpense($editUser, 'maktoom', $expense, '2026-09-27', false));
        $this->assertTrue(AccountPermissions::canDeleteExpense($deleteUser, 'maktoom', $expense, '2026-09-27', false));
    }

    public function test_admin_remains_a_full_override_for_historical_records(): void
    {
        $admin = $this->permissionUser([], true);
        $revenue = new AccountTransaction(['date' => '2020-01-01']);

        $this->assertTrue(AccountPermissions::canEditRevenue($admin, 'maktoom', $revenue, '2026-09-27', false));
        $this->assertTrue(AccountPermissions::canDeleteRevenue($admin, 'maktoom', $revenue, '2026-09-27', false));
        $this->assertTrue(AccountPermissions::canEditRevenueDateTime($admin, 'maktoom'));
        $this->assertTrue(AccountPermissions::canCreateBackdatedRevenue($admin, 'maktoom'));
        $this->assertTrue(AccountPermissions::canCreateBackdatedExpense($admin, 'maktoom'));
    }

    public function test_backdated_creation_requires_its_own_permission(): void
    {
        $manager = $this->permissionUser(['manage accounts revenues', 'manage accounts expenses']);
        $creator = $this->permissionUser([
            'create backdated account revenues',
            'create backdated account expenses',
        ]);

        $this->assertFalse(AccountPermissions::canCreateBackdatedRevenue($manager, 'maktoom'));
        $this->assertFalse(AccountPermissions::canCreateBackdatedExpense($manager, 'maktoom'));
        $this->assertTrue(AccountPermissions::canCreateBackdatedRevenue($creator, 'maktoom'));
        $this->assertTrue(AccountPermissions::canCreateBackdatedExpense($creator, 'maktoom'));
        $this->assertTrue(AccountPermissions::canCreateExpense($creator, 'maktoom'));
    }

    private function permissionUser(array $permissions, bool $isAdmin = false): User
    {
        $permissions[] = 'view accounts maktoom';
        $user = Mockery::mock(User::class);
        $user->shouldReceive('canAccessAccountBranch')->with('maktoom')->andReturn(true);
        $user->shouldReceive('can')->andReturnUsing(
            fn (string $permission): bool => in_array($permission, $permissions, true)
        );
        $user->shouldReceive('hasRole')->with('Admin')->andReturn($isAdmin);

        return $user;
    }
}
