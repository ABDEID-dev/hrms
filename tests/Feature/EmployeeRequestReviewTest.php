<?php

namespace Tests\Feature;

use App\Livewire\HumanResource\Messages\EmployeeRequests;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeRequestReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancel_review_closes_the_panel_and_discards_unsaved_fields(): void
    {
        $admin = User::factory()->make();
        $admin->setRelation('roles', collect([
            new Role(['name' => 'Admin', 'guard_name' => 'web']),
        ]));
        $this->actingAs($admin);

        Livewire::test(EmployeeRequests::class)
            ->set('selectedRequestId', 38)
            ->set('adminResponse', 'رد لم يتم حفظه')
            ->set('approvedAmount', 250)
            ->call('cancelReview')
            ->assertSet('selectedRequestId', null)
            ->assertSet('adminResponse', '')
            ->assertSet('approvedAmount', null);
    }
}
