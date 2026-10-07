<?php

namespace App\Livewire\HumanResource\Messages;

use App\Jobs\sendPendingMessages;
use App\Jobs\sendPendingMessagesByWhatsapp;
use App\Models\AdminAlert;
use App\Models\Discount;
use App\Models\Employee;
use App\Models\Message;
use App\Notifications\PersonalMessageNotification;
use App\Traits\MessageProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Number;
use Livewire\Component;
use Throwable;

class Personal extends Component
{
    use MessageProvider;

    // Variables - Start //
    public $accountBalance = ['status' => 400, 'balance' => '---', 'is_active' => '---'];

    public $messagesStatus = ['sent' => 0, 'unsent' => 0];

    public $batches;

    public $selectedBatch;

    public $employees = [];

    public $searchTerm;

    public Employee $selectedEmployee;

    public $messages = [];

    public $messageBody;

    public $managementAlert = [
        'body_ar' => '',
        'body_en' => '',
        'is_active' => true,
    ];

    public $currentManagementAlertId = null;
    // Variables - End //

    public function mount()
    {
        $this->selectedEmployee = Employee::first();
        $this->batches = Discount::where('is_sent', 0)
            ->distinct()
            ->pluck('batch')
            ->toArray();

        $this->loadManagementAlert();

        // try {
        //     $this->accountBalance = $this->CheckAccountBalance();
        // } catch (Throwable $th) {
        //     //
        // }
    }

    public function render()
    {
        $sent = Message::where('is_sent', 1)->count();
        $unsent = Message::where('is_sent', 0)->count();

        $this->messagesStatus = [
            'sent' => Number::format($sent ?? 0),
            'unsent' => Number::format($unsent ?? 0),
        ];

        $this->employees = Employee::where('id', 'like', '%'.$this->searchTerm.'%')
            ->orWhere('first_name', 'like', '%'.$this->searchTerm.'%')
            ->orWhere('last_name', 'like', '%'.$this->searchTerm.'%')
            ->get();
        $this->messages = Message::where('employee_id', $this->selectedEmployee->id)->get();

        $this->dispatch('initialize');

        return view('livewire.human-resource.messages.personal');
    }

    public function saveManagementAlert(): void
    {
        if (! Schema::hasTable('admin_alerts')) {
            session()->flash('error', __('Run the admin alerts migration first.'));

            return;
        }

        $this->validate([
            'managementAlert.body_ar' => 'nullable|string|max:5000',
            'managementAlert.body_en' => 'nullable|string|max:5000',
            'managementAlert.is_active' => 'boolean',
        ]);

        $bodyAr = trim((string) ($this->managementAlert['body_ar'] ?? ''));
        $bodyEn = trim((string) ($this->managementAlert['body_en'] ?? ''));

        if ($bodyAr === '' && $bodyEn === '') {
            $this->addError('managementAlert.body_ar', __('Enter Arabic or English alert text.'));

            return;
        }

        $alert = AdminAlert::query()->firstOrNew([
            'id' => $this->currentManagementAlertId,
        ]);

        $alert->fill([
            'body_ar' => $bodyAr ?: null,
            'body_en' => $bodyEn ?: null,
            'is_active' => (bool) ($this->managementAlert['is_active'] ?? true),
        ]);
        $alert->save();

        $this->currentManagementAlertId = $alert->id;
        $this->loadManagementAlert();

        session()->flash('success', __('Management alert updated successfully.'));
    }

    public function disableManagementAlert(): void
    {
        if (! Schema::hasTable('admin_alerts')) {
            return;
        }

        if (! $this->currentManagementAlertId) {
            return;
        }

        AdminAlert::query()->whereKey($this->currentManagementAlertId)->update([
            'is_active' => false,
        ]);

        $this->loadManagementAlert();
        session()->flash('success', __('Management alert hidden successfully.'));
    }

    public function selectEmployee(Employee $employee)
    {
        $this->selectedEmployee = $employee;
        $this->messages = Message::where('employee_id', $this->selectedEmployee->id)->get();
    }

    public function sendMessage()
    {
        $this->sendPersonalMessage('sms');
    }

    public function sendMessageByWhatsapp()
    {
        $this->sendPersonalMessage('whatsapp');
    }

    private function sendPersonalMessage(string $channel): void
    {
        $message = Message::create([
            'employee_id' => $this->selectedEmployee->id,
            'text' => $this->messageBody,
            'recipient' => $this->selectedEmployee->full_phone_number,
            'is_sent' => false,
        ]);

        $this->notifySelectedEmployee($message);

        $response = $channel === 'whatsapp'
            ? (new sendPendingMessagesByWhatsapp())->sendText($this->messageBody, $this->selectedEmployee->full_phone_number)
            : $this->sendSms($this->messageBody, $this->selectedEmployee->full_phone_number);

        if ($response === true) {
            $message->update([
                'is_sent' => true,
                'error' => $channel === 'whatsapp' ? 'Sent by WhatsApp API' : null,
            ]);
            $this->dispatch('playMessageSound');
        } else {
            $message->update(['is_sent' => false, 'error' => $response]);
            $this->dispatch('playErrorSound');
        }

        $this->reset('messageBody');
    }

    private function notifySelectedEmployee(Message $message): void
    {
        $recipientUser = $this->selectedEmployee->user;

        if (! $recipientUser || Auth::id() === $recipientUser->id) {
            return;
        }

        $recipientUser->notify(new PersonalMessageNotification($message, Auth::user()));
    }

    public function generateMessages()
    {
        $employeesDiscounts = Employee::with([
            'timelines',
            'discounts' => function ($query) {
                $query->where('is_sent', 0)->where('batch', $this->selectedBatch);
            },
        ]) /* ->whereHas('timelines', function ($query) {
            $query->where('department_id', 1)->where('end_date', null);
        }) */
            ->where('is_active', 1)
            ->whereHas('contract', function ($query) {
                $query->where('work_rate', 100);
            })
            ->get();

        $dates = explode(' to ', $this->selectedBatch);

        foreach ($employeesDiscounts as $employee) {
            $cashDiscountCount = 0;

            if ($employee->discounts) {
                foreach ($employee->discounts as $discount) {
                    $discount->rate > 0 ? ++$cashDiscountCount : '';

                    $discount->is_sent = 1;
                    $discount->save();
                }

                $messageBody =
                  'عزيزي صاحب المعرف رقم ('.
                  $employee->id.
                  ')، يرجى الاطلاع على التفاصيل التالية وذلك لغاية ('.
                  $dates[1].
                  '):

- الحسم المالي: '.
                  $cashDiscountCount.
                  '

- رصيد الإجازات: '.
                  $employee->max_leave_allowed.
                  '
- عداد الساعات: '.
                  Carbon::parse($employee->hourly_counter)->format('H:i').
                  '
- عداد التأخير: '.
                  Carbon::parse($employee->delay_counter)->format('H:i').
                  '

وجودك مهم،
مشروع الحماية المجتمعية.';

                Message::create([
                    'employee_id' => $employee->id,
                    'text' => $messageBody,
                    'recipient' => $employee->full_phone_number,
                    'is_sent' => false,
                ]);
            }
        }

        session()->flash('success', __('Generation complete! Your messages ready to fly!'));
        $this->batches = Discount::where('is_sent', 0)
            ->distinct()
            ->pluck('batch')
            ->toArray();
    }

    public function sendPendingMessages()
    {
        if (App::isDownForMaintenance() == 1) {
            Artisan::call('up');
            Log::info('Maintenance mode has been suspended.');
        }

        if ($this->messagesStatus['unsent'] != 0) {
            sendPendingMessages::dispatch();
            session()->flash('info', __("Let's go! Messages on their way!"));
        } else {
            $this->dispatch('toastr', type: 'info' /* , title: 'Done!' */, message: __('Everything has sent already!'));
        }
    }

    public function sendPendingMessagesByWhatsapp()
    {
        if ($this->messagesStatus['unsent'] != 0) {
            sendPendingMessagesByWhatsapp::dispatch();
            session()->flash('info', __("Let's go! Messages on their way!"));
        } else {
            $this->dispatch('toastr', type: 'info' /* , title: 'Done!' */, message: 'Everything has sent already!');
        }
    }

    private function loadManagementAlert(): void
    {
        if (! Schema::hasTable('admin_alerts')) {
            $this->currentManagementAlertId = null;
            $this->managementAlert = [
                'body_ar' => '',
                'body_en' => '',
                'is_active' => true,
            ];

            return;
        }

        $alert = AdminAlert::query()->latest()->first();

        $this->currentManagementAlertId = $alert?->id;
        $this->managementAlert = [
            'body_ar' => (string) ($alert->body_ar ?? ''),
            'body_en' => (string) ($alert->body_en ?? ''),
            'is_active' => (bool) ($alert->is_active ?? true),
        ];
    }
}
