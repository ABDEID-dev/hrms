<?php

namespace App\Livewire\HumanResource\Messages;

use App\Jobs\sendPendingBulkMessages;
use App\Models\BulkMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;
use Livewire\Component;

class Bulk extends Component
{
    public $messagesStatus = ['all' => 0, 'sent' => 0, 'unsent' => 0];

    public $numbersInput = '';

    public $messageText = '';

    public $numbers = [];

    public $validated = false;

    public $validationError = '';

    public function updatedNumbersInput()
    {
        $this->validated = false;
        $this->validationError = '';
        $this->numbers = [];
    }

    public function validateNumbers()
    {
        if (trim($this->messageText) === '') {
            session()->flash('error', 'حقل الرسالة لا يمكن أن يكون فارغًا.');
            $this->dispatch('scroll-to-top');
            $this->validated = false;

            return;
        }

        $lines = explode("\n", $this->numbersInput);
        $cleaned = [];
        $seenNumbers = [];
        $filteredInput = [];

        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            if ($trimmedLine === '') {
                continue;
            }

            $number = preg_replace('/\D/', '', $trimmedLine);

            // Accept international numbers without the plus sign, e.g. 971501234567.
            if (! preg_match('/^[1-9]\d{7,14}$/', $number)) {
                session()->flash(
                    'error',
                    "الرقم التالي غير صحيح: {$number}. يجب إدخال الرقم بصيغة دولية بدون علامة +، مثل 971501234567."
                );
                $this->dispatch('scroll-to-top');
                $this->validated = false;

                return;
            }

            if (in_array($number, $seenNumbers, true)) {
                session()->flash('error', "تم تكرار الرقم التالي: {$number}");
                $this->dispatch('scroll-to-top');
                $this->validated = false;

                return;
            }

            $seenNumbers[] = $number;
            $cleaned[] = $number.';';
            $filteredInput[] = $number;

            if (count($cleaned) > 50) {
                session()->flash('error', 'الرجاء الالتزام بإدخال 50 رقم فقط لا أكثر.');
                $this->dispatch('scroll-to-top');
                $this->validated = false;

                return;
            }
        }

        $this->numbersInput = implode("\n", $filteredInput);

        $this->numbers = $cleaned;
        $this->validated = true;
    }

    public function send()
    {
        if (! $this->validated) {
            $this->addError('general', 'الرجاء التحقق من الأرقام أولًا.');

            return;
        }

        if (empty($this->messageText)) {
            $this->addError('general', 'نص الرسالة فارغ.');

            return;
        }

        BulkMessage::create([
            'text' => $this->messageText,
            'numbers' => implode('', $this->numbers),
            'created_by' => Auth::user()->name,
            'updated_by' => Auth::user()->name,
        ]);

        session()->flash('success', 'تم حفظ الرسائل وسيتم إرسالها قريبًا.');
        $this->dispatch('scroll-to-top');

        $this->reset(['numbersInput', 'messageText', 'numbers', 'validated', 'validationError']);
    }

    public function render()
    {
        $createdBy = Auth::user()->name;

        $sent = BulkMessage::where('created_by', $createdBy)
            ->where('is_sent', 1)
            ->count();
        $unsent = BulkMessage::where('created_by', $createdBy)
            ->where('is_sent', 0)
            ->count();
        $all = BulkMessage::where('created_by', $createdBy)->count();

        $this->messagesStatus = [
            'sent' => Number::format($sent ?? 0),
            'unsent' => Number::format($unsent ?? 0),
            'all' => Number::format($all ?? 0),
        ];

        return view('livewire.human-resource.messages.bulk');
    }

    public function sendPendingBulkMessages()
    {
        if ($this->messagesStatus['unsent'] != 0) {
            sendPendingBulkMessages::dispatch();
            session()->flash('info', __("Let's go! Messages on their way!"));
        } else {
            $this->dispatch('toastr', type: 'info', message: __('Everything has sent already!'));
        }
    }
}
