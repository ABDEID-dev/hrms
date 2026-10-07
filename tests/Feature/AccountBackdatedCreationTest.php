<?php

namespace Tests\Feature;

use App\Livewire\Accounts\Expenses;
use App\Livewire\Accounts\Revenues;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class AccountBackdatedCreationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_authorized_user_can_create_backdated_revenue(): void
    {
        $this->freezeBusinessDay(false);
        $user = $this->userWithPermissions(['create backdated account revenues']);
        $this->actingAs($user);

        Livewire::test(Revenues::class, ['account' => 'maktoom'])
            ->set('revenueForm.employee_name', 'Test Employee')
            ->set('revenueForm.service', 'Historical Service')
            ->set('revenueForm.amount', 50)
            ->set('revenueForm.payment_method', 'cash')
            ->set('revenueForm.date', '2026-09-20')
            ->call('createRevenue')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('account_transactions', [
            'account' => 'maktoom',
            'type' => 'revenue',
            'service' => 'Historical Service',
            'date' => '2026-09-20',
        ]);
    }

    public function test_revenue_date_tampering_is_ignored_without_backdated_permission(): void
    {
        $this->freezeBusinessDay();
        $user = $this->userWithPermissions(['create account revenues']);
        $this->actingAs($user);

        Livewire::test(Revenues::class, ['account' => 'maktoom'])
            ->set('revenueForm.employee_name', 'Test Employee')
            ->set('revenueForm.service', 'Current Service')
            ->set('revenueForm.amount', 50)
            ->set('revenueForm.payment_method', 'cash')
            ->set('revenueForm.date', '2026-09-20')
            ->call('createRevenue')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('account_transactions', [
            'account' => 'maktoom',
            'type' => 'revenue',
            'service' => 'Current Service',
            'date' => '2026-09-27',
        ]);
    }

    public function test_authorized_user_can_create_backdated_expense(): void
    {
        $this->freezeBusinessDay(false);
        $user = $this->userWithPermissions(['create backdated account expenses']);
        $this->actingAs($user);

        Livewire::test(Expenses::class, ['account' => 'maktoom'])
            ->set('expenseForm.service', 'Historical Purchase')
            ->set('expenseForm.has_invoice', '0')
            ->set('expenseForm.quantity', 1)
            ->set('expenseForm.unit_price', 25)
            ->set('expenseForm.date', '2026-09-18')
            ->call('createExpense')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('account_transactions', [
            'account' => 'maktoom',
            'type' => 'expense',
            'service' => 'Historical Purchase',
            'date' => '2026-09-18',
        ]);
    }

    public function test_expense_date_tampering_is_ignored_without_backdated_permission(): void
    {
        $this->freezeBusinessDay();
        $user = $this->userWithPermissions(['create account expenses']);
        $this->actingAs($user);

        Livewire::test(Expenses::class, ['account' => 'maktoom'])
            ->set('expenseForm.service', 'Current Purchase')
            ->set('expenseForm.has_invoice', '0')
            ->set('expenseForm.quantity', 1)
            ->set('expenseForm.unit_price', 25)
            ->set('expenseForm.date', '2026-09-18')
            ->call('createExpense')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('account_transactions', [
            'account' => 'maktoom',
            'type' => 'expense',
            'service' => 'Current Purchase',
            'date' => '2026-09-27',
        ]);
    }

    private function freezeBusinessDay(bool $businessWindowOpen = true): void
    {
        $time = $businessWindowOpen ? '13:00:00' : '08:00:00';
        Carbon::setTestNow(Carbon::parse('2026-09-27 '.$time, 'Asia/Dubai'));
    }

    private function userWithPermissions(array $permissions): User
    {
        $permissions[] = 'view accounts maktoom';
        $user = User::factory()->make();
        $user->setRelation('roles', collect());

        Gate::before(fn ($authenticatedUser, $ability) => $authenticatedUser === $user && in_array($ability, $permissions, true)
            ? true
            : null);

        return $user;
    }
}
