<?php

namespace Tests\Unit;

use App\Livewire\Employee\ManagementResponses;
use App\Livewire\HumanResource\Messages\EmployeeRequests;
use App\Models\EmployeeRequest;
use ReflectionMethod;
use Tests\TestCase;

class ManagementResponsesNotificationTest extends TestCase
{
    public function test_notification_matching_the_first_line_of_internal_message_is_hidden_as_duplicate(): void
    {
        $component = new ManagementResponses();
        $component->recentMessages = collect([
            (object) ['text' => "تمت الموافقة على طلبك.\nنوع الطلب: سلفة\nالمبلغ: 500.00"],
        ]);

        $method = new ReflectionMethod(ManagementResponses::class, 'notificationAppearsInInternalMessages');

        $this->assertTrue($method->invoke($component, 'تمت الموافقة على طلبك.'));
    }

    public function test_notification_with_different_text_is_not_hidden(): void
    {
        $component = new ManagementResponses();
        $component->recentMessages = collect([
            (object) ['text' => "تمت الموافقة على طلبك.\nنوع الطلب: سلفة"],
        ]);

        $method = new ReflectionMethod(ManagementResponses::class, 'notificationAppearsInInternalMessages');

        $this->assertFalse($method->invoke($component, 'يرجى مراجعة طلب آخر.'));
    }

    public function test_internal_advance_message_does_not_repeat_amount_or_status(): void
    {
        $component = new EmployeeRequests();
        $request = new EmployeeRequest([
            'type' => 'advance',
            'status' => 'approved',
            'amount' => 500,
            'admin_response' => 'سيتم خصم المبلغ من الراتب.',
        ]);
        $method = new ReflectionMethod(EmployeeRequests::class, 'buildEmployeeMessageBody');

        $message = $method->invoke($component, $request, 'تم اعتماد السلفة بمبلغ 500.00.');

        $this->assertSame(1, substr_count($message, '500.00'));
        $this->assertStringNotContainsString(__('ui.current_status').':', $message);
        $this->assertStringContainsString('سيتم خصم المبلغ من الراتب.', $message);
    }
}
