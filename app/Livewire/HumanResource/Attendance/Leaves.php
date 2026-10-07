<?php

namespace App\Livewire\HumanResource\Attendance;

use App\Models\Employee;
use Livewire\Component;

class Leaves extends Component
{
    public $employees;

    public ?int $selectedEmployeeId = null;

    public ?int $selectedDay = null;

    public array $daysOfWeek = [
        6 => 'السبت',
        0 => 'الأحد',
        1 => 'الاثنين',
        2 => 'الثلاثاء',
        3 => 'الأربعاء',
        4 => 'الخميس',
        5 => 'الجمعة',
    ];

    public function mount(): void
    {
        $this->loadEmployees();

        $this->selectedEmployeeId = $this->employees->first()?->id;
        $this->selectedDay = 6;
    }

    public function render()
    {
        return view('livewire.human-resource.attendance.leaves');
    }

    public function createWeeklyHoliday(): void
    {
        $this->validate(
            [
                'selectedEmployeeId' => ['required', 'exists:employees,id'],
                'selectedDay' => ['required', 'integer', 'in:0,1,2,3,4,5,6'],
            ],
            [],
            [
                'selectedEmployeeId' => 'الموظف',
                'selectedDay' => 'يوم الإجازة',
            ]
        );

        Employee::findOrFail($this->selectedEmployeeId)->update([
            'weekly_holiday' => $this->selectedDay,
        ]);

        $this->loadEmployees();

        session()->flash('success', 'تم إنشاء الإجازة الأسبوعية بنجاح.');
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function clearWeeklyHoliday(int $employeeId): void
    {
        Employee::findOrFail($employeeId)->update([
            'weekly_holiday' => null,
        ]);

        $this->loadEmployees();

        session()->flash('success', 'تم إزالة الإجازة الأسبوعية للموظف.');
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function getHolidayName(?int $day): string
    {
        return $day === null ? 'لم يتم التحديد' : ($this->daysOfWeek[$day] ?? 'غير معروف');
    }

    private function loadEmployees(): void
    {
        $this->employees = Employee::query()
            ->where('is_active', 1)
            ->orderBy('first_name')
            ->get();
    }
}
