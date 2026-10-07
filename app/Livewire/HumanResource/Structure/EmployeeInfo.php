<?php

namespace App\Livewire\HumanResource\Structure;

use App\Models\Center;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Timeline;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class EmployeeInfo extends Component
{
    use WithFileUploads;

    public $centers;

    public $departments;

    public $positions;

    public $employee;

    public $timeline;

    public $employeeTimelines;

    public $employeeTimelineInfo = [];

    public $employeeAssets;

    public $isEdit = false;

    public $confirmedId;

    public $selectedCenter;

    public $selectedDepartment;

    public $selectedPosition;

    public $profilePhoto;

    // 👉 Mount
    public function mount($id)
    {
        $id = $id ?: Auth::user()?->employee_id;
        $this->employee = Employee::with(['contract', 'user'])->findOrFail($id);

        if (! $this->canViewEmployeeProfile()) {
            abort(403);
        }

        $this->employeeAssets = $this->employee
            ->transitions()
            ->with('asset')
            ->orderBy('handed_date', 'desc')
            ->get();
        // dd($this->employeeAssets);
        $this->centers = Center::all();
        $this->departments = department::all();
        $this->positions = Position::all();
    }

    // 👉 Render
    public function render()
    {
        $this->employeeTimelines = Timeline::with(['center', 'department', 'position'])
            ->where('employee_id', $this->employee->id)
            ->orderBy('id', 'desc')
            ->get();

        $currentMonth = Carbon::now(config('app.timezone', 'Asia/Dubai'))->format('Y-m');
        $monthlyDiscountsTotal = (float) $this->employee->discounts()
            ->where('batch', $currentMonth)
            ->sum('rate');
        $monthlyDiscountsCount = $this->employee->discounts()
            ->where('batch', $currentMonth)
            ->count();
        $latestDiscounts = $this->employee->discounts()
            ->latest('date')
            ->latest('id')
            ->take(5)
            ->get();
        $totalSalary = (float) $this->employee->basic_salary
            + (float) $this->employee->housing_allowance
            + (float) $this->employee->transportation_allowance;
        $salarySummary = [
            'basic' => (float) $this->employee->basic_salary,
            'housing' => (float) $this->employee->housing_allowance,
            'transportation' => (float) $this->employee->transportation_allowance,
            'total' => $totalSalary,
            'net' => $totalSalary - $monthlyDiscountsTotal,
        ];

        return view('livewire.human-resource.structure.employee-info', compact(
            'currentMonth',
            'latestDiscounts',
            'monthlyDiscountsCount',
            'monthlyDiscountsTotal',
            'salarySummary'
        ));
    }

    public function updateProfilePhoto()
    {
        $this->ensureCanManageEmployeeProfile();

        $this->validate([
            'profilePhoto' => ['required', 'image', 'max:4096'],
        ]);

        $oldEmployeePhoto = $this->employee->profile_photo_path;
        $oldUserPhoto = $this->employee->user?->profile_photo_path;
        $newPath = $this->profilePhoto->store('profile-photos', 'public');

        $this->employee->profile_photo_path = $newPath;
        $this->employee->save();

        if ($this->employee->user) {
            $this->employee->user->profile_photo_path = $newPath;
            $this->employee->user->save();
        }

        foreach (array_unique(array_filter([$oldEmployeePhoto, $oldUserPhoto])) as $oldPhoto) {
            if ($oldPhoto !== $newPath && $oldPhoto !== 'profile-photos/.default-photo.jpg' && Storage::disk('public')->exists($oldPhoto)) {
                Storage::disk('public')->delete($oldPhoto);
            }
        }

        $this->reset('profilePhoto');
        $this->employee = $this->employee->fresh(['contract', 'user']);

        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    // 👉 Toggle active status
// المبرمج عبدالله عيد

    public function toggleActive()
    {
        if (! Auth::user()?->hasRole('Admin')) {
            abort(403);
        }

        $presentTimeline = $this->employee
            ->timelines()
            ->orderBy('timelines.id', 'desc')
            ->first();

        if ($this->employee->is_active == true) {

            $this->employee->is_active = false;

            if ($presentTimeline) {
                $presentTimeline->end_date = Carbon::now();
                $presentTimeline->save();
            }

        } else {

            $this->employee->is_active = true;

            if ($presentTimeline) {
                $presentTimeline->end_date = null;
                $presentTimeline->save();
            }
        }

        $this->employee->save();

        $this->dispatch(
            'toastr',
            type: 'success',
            message: __('Going Well!')
        );
    }
    // 👉 Submit timeline
    public function submitTimeline()
    {
        $this->ensureCanManageEmployeeProfile();

        $this->employeeTimelineInfo['centerId'] = $this->selectedCenter;
        $this->employeeTimelineInfo['departmentId'] = $this->selectedDepartment;
        $this->employeeTimelineInfo['positionId'] = $this->selectedPosition;

        $this->validate([
            'employeeTimelineInfo.centerId' => 'required',
            'employeeTimelineInfo.departmentId' => 'required',
            'employeeTimelineInfo.positionId' => 'required',
            'employeeTimelineInfo.startDate' => 'required',
            'employeeTimelineInfo.isSequent' => 'required',
        ]);

        $this->isEdit ? $this->updateTimeline() : $this->storeTimeline();
    }

    // 👉 Store timeline
    public function showStoreTimelineModal()
    {
        $this->ensureCanManageEmployeeProfile();

        $this->reset('isEdit', 'selectedCenter', 'selectedDepartment', 'selectedPosition', 'employeeTimelineInfo');
        $this->dispatch('clearSelect2Values');
    }

    public function storeTimeline()
    {
        $this->ensureCanManageEmployeeProfile();

        DB::beginTransaction();
        try {
            $presentTimeline = $this->employee
                ->timelines()
                ->orderBy('timelines.id', 'desc')
                ->first();

            if ($presentTimeline) {
                $presentTimeline->end_date = Carbon::now();
                $presentTimeline->save();
            }

            $timelineData = [
                'employee_id' => $this->employee->id,
                'center_id' => $this->employeeTimelineInfo['centerId'],
                'department_id' => $this->employeeTimelineInfo['departmentId'],
                'position_id' => $this->employeeTimelineInfo['positionId'],
                'start_date' => $this->employeeTimelineInfo['startDate'],
                'end_date' => isset($this->employeeTimelineInfo['endDate']) ? $this->employeeTimelineInfo['endDate'] : null,
                'notes' => isset($this->employeeTimelineInfo['notes']) ? $this->employeeTimelineInfo['notes'] : null,
            ];

            if (Schema::hasColumn('timelines', 'is_sequent')) {
                $timelineData['is_sequent'] = $this->employeeTimelineInfo['isSequent'];
            }

            Timeline::create($timelineData);

            $this->dispatch('closeModal', elementId: '#timelineModal');
            $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $this->dispatch(
                'toastr',
                type: 'success' /* , title: 'Done!' */,
                message: 'Something is going wrong, check the log file!'
            );
            throw $e;
        }
    }

    // 👉 Update timeline
    public function showUpdateTimelineModal(Timeline $timeline)
    {
        $this->ensureCanManageEmployeeProfile();

        $this->isEdit = true;

        $this->timeline = $timeline;

        $this->employeeTimelineInfo['centerId'] = $timeline->center_id;
        $this->employeeTimelineInfo['departmentId'] = $timeline->department_id;
        $this->employeeTimelineInfo['positionId'] = $timeline->position_id;
        $this->employeeTimelineInfo['startDate'] = $timeline->start_date;
        $this->employeeTimelineInfo['endDate'] = $timeline->end_date;
        $this->employeeTimelineInfo['isSequent'] = Schema::hasColumn('timelines', 'is_sequent')
            ? $timeline->is_sequent
            : 1;
        $this->employeeTimelineInfo['notes'] = $timeline->notes;

        $this->dispatch(
            'setSelect2Values',
            centerId: $timeline->center_id,
            departmentId: $timeline->department_id,
            positionId: $timeline->position_id
        );
    }

    public function updateTimeline()
    {
        $this->ensureCanManageEmployeeProfile();

        $timelineData = [
            'center_id' => $this->employeeTimelineInfo['centerId'],
            'department_id' => $this->employeeTimelineInfo['departmentId'],
            'position_id' => $this->employeeTimelineInfo['positionId'],
            'start_date' => $this->employeeTimelineInfo['startDate'],
            'end_date' => $this->employeeTimelineInfo['endDate'],
            'notes' => $this->employeeTimelineInfo['notes'],
        ];

        if (Schema::hasColumn('timelines', 'is_sequent')) {
            $timelineData['is_sequent'] = $this->employeeTimelineInfo['isSequent'];
        }

        $this->timeline->update($timelineData);

        $this->dispatch('closeModal', elementId: '#timelineModal');
        $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));
    }

    // 👉 Delete timeline
    public function confirmDeleteTimeline(Timeline $timeline)
    {
        $this->ensureCanManageEmployeeProfile();

        $this->confirmedId = $timeline->id;
    }

    public function deleteTimeline(Timeline $timeline)
    {
        $this->ensureCanManageEmployeeProfile();

        $timeline->delete();

        $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));
    }

    // 👉 Set present timeline
    public function setPresentTimeline(Timeline $timeline)
    {
        $this->ensureCanManageEmployeeProfile();

        $timeline->end_date = null;
        $timeline->save();

        session()->flash('success', __('The current position assigned successfully.'));
    }

    public function canManageEmployeeProfile(): bool
    {
        return Auth::user()?->hasAnyRole(['Admin', 'HR']) ?? false;
    }

    private function canViewEmployeeProfile(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $this->canManageEmployeeProfile()
            || $user->can('view employee details')
            || (int) $user->employee_id === (int) $this->employee->id;
    }

    private function ensureCanManageEmployeeProfile(): void
    {
        if (! $this->canManageEmployeeProfile()) {
            abort(403);
        }
    }
}
