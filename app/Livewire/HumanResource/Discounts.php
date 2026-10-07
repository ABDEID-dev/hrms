<?php

namespace App\Livewire\HumanResource;

use App\Models\Discount;
use App\Models\Employee;
use Carbon\Carbon;
use Livewire\Component;

class Discounts extends Component
{
    private const OFFICIAL_TIMEZONE = 'Asia/Dubai';

    public $employees;

    public $discounts;

    public string $selectedDate;

    public ?int $selectedEmployeeId = null;

    public ?int $confirmedDiscountId = null;

    public array $discountForm = [
        'amount' => null,
        'reason' => '',
    ];

    public function mount(): void
    {
        $this->selectedDate = Carbon::today(self::OFFICIAL_TIMEZONE)->toDateString();
        $this->loadEmployees();
        $this->loadDiscounts();

        $this->selectedEmployeeId = $this->employees->first()?->id;
    }

    public function render()
    {
        return view('livewire.human-resource.discounts');
    }

    public function createDiscount(): void
    {
        $this->validate(
            [
                'selectedEmployeeId' => ['required', 'exists:employees,id'],
                'selectedDate' => ['required', 'date'],
                'discountForm.amount' => ['required', 'integer', 'min:1'],
                'discountForm.reason' => ['required', 'string', 'max:255'],
            ],
            [],
            [
                'selectedEmployeeId' => 'الموظف',
                'discountForm.amount' => 'مبلغ الخصم',
                'discountForm.reason' => 'سبب الخصم',
            ]
        );

        $selectedDate = Carbon::parse($this->selectedDate, self::OFFICIAL_TIMEZONE);

        Discount::create([
            'employee_id' => $this->selectedEmployeeId,
            'rate' => $this->discountForm['amount'],
            'date' => $selectedDate->toDateString(),
            'reason' => $this->discountForm['reason'],
            'is_auto' => false,
            'is_sent' => false,
            'batch' => $selectedDate->format('Y-m'),
        ]);

        $this->reset('discountForm');
        $this->loadDiscounts();

        session()->flash('success', 'تم إضافة الخصم بنجاح.');
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function confirmDeleteDiscount(int $discountId): void
    {
        $this->confirmedDiscountId = $discountId;
    }

    public function deleteDiscount(): void
    {
        Discount::findOrFail($this->confirmedDiscountId)->delete();

        $this->confirmedDiscountId = null;
        $this->loadDiscounts();

        session()->flash('success', 'تم حذف الخصم بنجاح.');
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function showPreviousDay(): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate, self::OFFICIAL_TIMEZONE)->subDay()->toDateString();
        $this->loadDiscounts();
    }

    public function showNextDay(): void
    {
        if (! $this->canShowNextDay()) {
            return;
        }

        $this->selectedDate = Carbon::parse($this->selectedDate, self::OFFICIAL_TIMEZONE)->addDay()->toDateString();
        $this->loadDiscounts();
    }

    public function showToday(): void
    {
        $this->selectedDate = Carbon::today(self::OFFICIAL_TIMEZONE)->toDateString();
        $this->loadDiscounts();
    }

    public function canShowNextDay(): bool
    {
        return Carbon::parse($this->selectedDate)->lt(Carbon::today(self::OFFICIAL_TIMEZONE));
    }

    public function updatedSelectedDate(): void
    {
        try {
            $this->selectedDate = Carbon::parse($this->selectedDate, self::OFFICIAL_TIMEZONE)->toDateString();

            if (Carbon::parse($this->selectedDate)->gt(Carbon::today(self::OFFICIAL_TIMEZONE))) {
                $this->selectedDate = Carbon::today(self::OFFICIAL_TIMEZONE)->toDateString();
            }

            $this->loadDiscounts();
        } catch (\Throwable) {
            $this->selectedDate = Carbon::today(self::OFFICIAL_TIMEZONE)->toDateString();
            $this->loadDiscounts();
        }
    }

    private function loadEmployees(): void
    {
        $this->employees = Employee::query()
            ->where('is_active', 1)
            ->orderBy('first_name')
            ->get();
    }

    private function loadDiscounts(): void
    {
        $this->discounts = Discount::query()
            ->with('employee')
            ->whereDate('date', $this->selectedDate)
            ->latest('date')
            ->latest('id')
            ->get();
    }
}
