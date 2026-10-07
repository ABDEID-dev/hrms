<?php

namespace App\Livewire\HumanResource\Structure;

use App\Models\Contract;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Employees extends Component
{
    use WithFileUploads;
    use WithPagination;

    // 👉 Variables
    public $searchTerm = null;

    public $contracts;

    public $employee;

    public $employeeInfo = [];

    public $availableRoles;

    public $profilePhoto;

    public $isEdit = false;

    public $confirmedId;

    // 👉 Mount
    public function mount()
    {
        $this->contracts = Contract::all();
        $this->availableRoles = Role::query()
            ->when(! $this->canAssignPrimaryAdminRole(), fn ($query) => $query->where('name', '!=', 'Admin'))
            ->orderBy('name')
            ->get();
    }

    // 👉 Render
    public function render()
    {
        $employees = Employee::query()
            ->when(! Auth::user()?->hasRole('Admin'), fn ($query) => $query->where('id', '!=', 1))
            ->where(function ($query) {
                $search = '%'.$this->searchTerm.'%';

                $query
                    ->where('id', 'like', $search)
                    ->orWhere('first_name', 'like', $search)
                    ->orWhere('last_name', 'like', $search);
            })
            ->paginate(20);

        return view('livewire.human-resource.structure.employees', [
            'employees' => $employees,
        ]);
    }

    // 👉 Submit employee
    public function submitEmployee()
    {
        $this->isEdit ? $this->ensureCanEditEmployees() : $this->ensureCanCreateEmployees();

        $this->normalizePhoneInputs();
        $this->normalizeIdentityDocumentInput();
        $this->normalizePermissionInputs();
        $this->assignNextEmployeeIdIfNeeded();

        $this->validate([
            'employeeInfo.id' => [
                'required',
                'integer',
                Rule::unique('employees', 'id')
                    ->ignore($this->isEdit ? $this->employee?->id : null, 'id'),
            ],
            'employeeInfo.contractId' => 'required',
            'employeeInfo.fullName' => 'required|string|max:255',
            'employeeInfo.age' => 'required|integer|min:1|max:120',
            'employeeInfo.nationalNumber' => [
                'required',
                'string',
                'max:20',
                Rule::unique('employees', 'national_number')
                    ->ignore($this->isEdit ? $this->employee?->id : null, 'id'),

                function ($attribute, $value, $fail) {
                    $isEmiratesId = preg_match('/^784-\d{4}-\d{7}-\d$/', $value);
                    $isPassport = preg_match('/^[A-Z0-9]{5,20}$/', $value);

                    if (! $isEmiratesId && ! $isPassport) {
                        $fail(__('Enter a valid Emirates ID in the format 784-0000-0000000-0 or a passport number.'));
                    }
                },
            ],
            'employeeInfo.phoneCountryCode' => 'required|string|min:1|max:5',
            'employeeInfo.mobileNumber' => [
                'required',
                'string',
                'min:6',
                'max:15',
                'regex:/^[0-9]+$/',
                Rule::unique('employees', 'mobile_number')
                    ->where(fn ($query) => $query->where('phone_country_code', $this->employeeInfo['phoneCountryCode']))
                    ->ignore($this->employeeInfo['id'] ?? null, 'id'),
            ],
            'employeeInfo.gender' => 'required',
            'employeeInfo.username' => [
                $this->isEdit ? 'nullable' : 'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($this->employee?->user?->id),
            ],
            'employeeInfo.password' => [$this->isEdit ? 'nullable' : 'required', 'string', 'min:8'],
            'employeeInfo.accessRole' => ['required', Rule::exists('roles', 'name')],
            'employeeInfo.nationality' => 'nullable',
            'employeeInfo.jobTitle' => 'nullable',
            'employeeInfo.jobTitleEn' => 'nullable',
            'employeeInfo.jobSpecialization' => 'nullable',
            'employeeInfo.basicSalary' => 'nullable|numeric|min:0',
            'employeeInfo.housingAllowance' => 'nullable|numeric|min:0',
            'employeeInfo.transportationAllowance' => 'nullable|numeric|min:0',
            'employeeInfo.visaType' => 'nullable|in:external_residency,internal_residency,temporary_visit',
            'employeeInfo.requiresAttendanceLocation' => 'boolean',
            'profilePhoto' => ['nullable', 'image', 'max:4096'],
        ], [
            'employeeInfo.id.unique' => 'رقم المعرف موجود بالفعل ولا يمكن استخدامه مرة أخرى. برجاء تغيير رقم ID، أو التواصل مع المبرمج المختص عبدالله لحذف الـ ID القديم أو تغيير رقم ID.',
            'employeeInfo.id.required' => 'برجاء إدخال رقم المعرف.',
            'employeeInfo.id.integer' => 'رقم المعرف يجب أن يكون رقمًا صحيحًا.',
        ]);

        $this->isEdit ? $this->editEmployee() : $this->addEmployee();
    }

    // 👉 Store employee
    public function showCreateEmployeeModal()
    {
        $this->ensureCanCreateEmployees();

        $this->reset('isEdit', 'employeeInfo', 'profilePhoto');
        $this->employeeInfo['phoneCountryCode'] = '971';
        $this->employeeInfo['id'] = $this->nextAvailableEmployeeId();
        $this->employeeInfo['accessRole'] = 'Employee';
        $this->employeeInfo['requiresAttendanceLocation'] = true;
        $this->employeeInfo['password'] = '';
    }

    public function addEmployee()
    {
        $this->ensureCanCreateEmployees();

        if (Employee::query()->whereKey($this->employeeInfo['id'])->exists()) {
            throw ValidationException::withMessages([
                'employeeInfo.id' => 'رقم المعرف موجود بالفعل ولا يمكن استخدامه مرة أخرى. برجاء تغيير رقم ID، أو التواصل مع المبرمج المختص عبدالله لحذف الـ ID القديم أو تغيير رقم ID.',
            ]);
        }

        $createdEmployee = Employee::create([
            'id' => $this->employeeInfo['id'],
            'contract_id' => $this->employeeInfo['contractId'],
            'first_name' => trim($this->employeeInfo['fullName']),
            'father_name' => '',
            'last_name' => '',
            'mother_name' => '',
            'birth_and_place' => '',
            'national_number' => $this->employeeInfo['nationalNumber'],
            'phone_country_code' => $this->employeeInfo['phoneCountryCode'],
            'mobile_number' => $this->employeeInfo['mobileNumber'],
            'age' => $this->employeeInfo['age'],
            'degree' => '-',
            'gender' => $this->employeeInfo['gender'],
            'address' => '-',
            'nationality' => $this->employeeInfo['nationality'] ?? null,
            'job_title' => $this->employeeInfo['jobTitle'] ?? null,
            'job_title_en' => $this->employeeInfo['jobTitleEn'] ?? null,
            'job_specialization' => $this->employeeInfo['jobSpecialization'] ?? null,
            'basic_salary' => $this->employeeInfo['basicSalary'] ?? null,
            'housing_allowance' => $this->employeeInfo['housingAllowance'] ?? null,
            'transportation_allowance' => $this->employeeInfo['transportationAllowance'] ?? null,
            'visa_type' => $this->employeeInfo['visaType'] ?? null,
            'requires_attendance_location' => (bool) ($this->employeeInfo['requiresAttendanceLocation'] ?? true),
            'notes' => isset($this->employeeInfo['notes']) ? $this->employeeInfo['notes'] : null,
            'profile_photo_path' => 'profile-photos/.default-photo.jpg',
        ]);

        $this->syncEmployeeUserAccount($createdEmployee);
        $this->syncEmployeeProfilePhoto($createdEmployee->fresh(['user']));

        $this->dispatch('closeModal', elementId: '#employeeModal');
        $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));

        session()->flash('openTimelineModal', true);

        return redirect()->route('structure-employees-info', ['id' => $createdEmployee->id]);
    }

    // 👉 Update employee
    public function showEditEmployeeModal(Employee $employee)
    {
        $this->ensureCanEditEmployees();

        $this->isEdit = true;
        $this->reset('profilePhoto');

        $this->employee = $employee;

        $this->employeeInfo['id'] = $employee->id;
        $this->employeeInfo['contractId'] = $employee->contract_id;
        $this->employeeInfo['fullName'] = $employee->full_name;
        $this->employeeInfo['age'] = $employee->age;
        $this->employeeInfo['nationalNumber'] = $employee->national_number;
        $this->employeeInfo['phoneCountryCode'] = $employee->phone_country_code ?: '971';
        $this->employeeInfo['mobileNumber'] = $employee->mobile_number;
        $this->employeeInfo['gender'] = $employee->gender;
        $this->employeeInfo['nationality'] = $employee->nationality;
        $this->employeeInfo['jobTitle'] = $employee->job_title;
        $this->employeeInfo['jobTitleEn'] = $employee->job_title_en;
        $this->employeeInfo['jobSpecialization'] = $employee->job_specialization;
        $this->employeeInfo['basicSalary'] = $employee->basic_salary;
        $this->employeeInfo['housingAllowance'] = $employee->housing_allowance;
        $this->employeeInfo['transportationAllowance'] = $employee->transportation_allowance;
        $this->employeeInfo['visaType'] = $employee->visa_type;
        $this->employeeInfo['requiresAttendanceLocation'] = (bool) $employee->requires_attendance_location;
        $this->employeeInfo['notes'] = $employee->notes;
        $this->employeeInfo['username'] = $employee->user?->username;
        $this->employeeInfo['password'] = $employee->user?->visible_password ?? '';
        $this->employeeInfo['accessRole'] = $employee->user?->roles->pluck('name')->first() ?? 'Employee';
    }

    public function editEmployee()
    {
        $this->ensureCanEditEmployees();

        $this->employee->update([
            'id' => $this->employeeInfo['id'],
            'contract_id' => $this->employeeInfo['contractId'],
            'first_name' => trim($this->employeeInfo['fullName']),
            'father_name' => '',
            'last_name' => '',
            'mother_name' => '',
            'birth_and_place' => '',
            'national_number' => $this->employeeInfo['nationalNumber'],
            'phone_country_code' => $this->employeeInfo['phoneCountryCode'],
            'mobile_number' => $this->employeeInfo['mobileNumber'],
            'age' => $this->employeeInfo['age'],
            'degree' => $this->employee->degree ?: '-',
            'gender' => $this->employeeInfo['gender'],
            'address' => $this->employee->address ?: '-',
            'nationality' => $this->employeeInfo['nationality'] ?? null,
            'job_title' => $this->employeeInfo['jobTitle'] ?? null,
            'job_title_en' => $this->employeeInfo['jobTitleEn'] ?? null,
            'job_specialization' => $this->employeeInfo['jobSpecialization'] ?? null,
            'basic_salary' => $this->employeeInfo['basicSalary'] ?? null,
            'housing_allowance' => $this->employeeInfo['housingAllowance'] ?? null,
            'transportation_allowance' => $this->employeeInfo['transportationAllowance'] ?? null,
            'visa_type' => $this->employeeInfo['visaType'] ?? null,
            'requires_attendance_location' => (bool) ($this->employeeInfo['requiresAttendanceLocation'] ?? true),
            'notes' => isset($this->employeeInfo['notes']) ? $this->employeeInfo['notes'] : null,
        ]);

        $this->syncEmployeeUserAccount($this->employee->fresh());
        $this->syncEmployeeProfilePhoto($this->employee->fresh(['user']));

        $this->dispatch('closeModal', elementId: '#employeeModal');
        $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));
    }

    // 👉 Delete employee
    public function confirmDeleteEmployee($id)
    {
        $this->ensureCanDeleteEmployees();

        $this->confirmedId = $id;
    }

    public function deleteEmployee(Employee $employee)
    {
        $this->ensureCanDeleteEmployees();
        abort_if((int) $employee->id === 1, 403);

        $employee->delete();
        $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));
    }

    private function assignNextEmployeeIdIfNeeded(): void
    {
        if ($this->isEdit) {
            return;
        }

        $currentId = (int) ($this->employeeInfo['id'] ?? 0);

        if ($currentId > 0 && ! Employee::withTrashed()->whereKey($currentId)->exists()) {
            return;
        }

        $this->employeeInfo['id'] = $this->nextAvailableEmployeeId();
    }

    private function nextAvailableEmployeeId(): int
    {
        $nextId = 1;

        Employee::withTrashed()
            ->orderBy('id')
            ->pluck('id')
            ->each(function ($id) use (&$nextId) {
                $id = (int) $id;

                if ($id < $nextId) {
                    return;
                }

                if ($id === $nextId) {
                    $nextId++;
                }
            });

        return $nextId;
    }

    private function normalizePhoneInputs(): void
    {
        $this->employeeInfo['phoneCountryCode'] = Employee::normalizeCountryCode($this->employeeInfo['phoneCountryCode'] ?? '971');
        $this->employeeInfo['mobileNumber'] = Employee::normalizeLocalPhone($this->employeeInfo['mobileNumber'] ?? '');
    }

    private function normalizeIdentityDocumentInput(): void
    {
        $value = trim((string) ($this->employeeInfo['nationalNumber'] ?? ''));
        $value = preg_replace('/\s+/', '', $value);

        if (str_starts_with($value, '784')) {
            $digitsOnly = preg_replace('/\D+/', '', $value);

            if (strlen($digitsOnly) === 15) {
                $value = substr($digitsOnly, 0, 3).'-'.substr($digitsOnly, 3, 4).'-'.substr($digitsOnly, 7, 7).'-'.substr($digitsOnly, 14, 1);
            }
        } else {
            $value = strtoupper($value);
        }

        $this->employeeInfo['nationalNumber'] = $value;
    }

    private function syncEmployeeUserAccount(Employee $employee): void
    {
        $fullPhoneNumber = $employee->full_phone_number ?: null;
        $user = $employee->user ?: new User();
        $isNewUser = ! $user->exists;

        $user->fill([
            'name' => trim($this->employeeInfo['fullName']),
            'employee_id' => $employee->id,
            'mobile' => $fullPhoneNumber,
            'username' => $this->employeeInfo['username'] ?? $user->username ?? (string) $employee->id,
            'email' => $user->email,
            'profile_photo_path' => $user->profile_photo_path ?: 'profile-photos/.default-photo.jpg',
        ]);

        $plainPassword = trim((string) ($this->employeeInfo['password'] ?? ''));

        if ($plainPassword !== '') {
            $user->password = Hash::make($plainPassword);
            $user->visible_password = $plainPassword;
        } elseif (! $user->exists) {
            $user->password = Hash::make('12345678');
            $user->visible_password = '12345678';
        }

        $user->save();

        if ($this->canAssignEmployeePermissions() || $isNewUser) {
            $user->syncRoles([
                Role::firstOrCreate([
                    'name' => $this->canAssignEmployeePermissions() ? $this->employeeInfo['accessRole'] : 'Employee',
                    'guard_name' => 'web',
                ]),
            ]);
        }

    }

    private function syncEmployeeProfilePhoto(Employee $employee): void
    {
        if (! $this->profilePhoto) {
            return;
        }

        $oldEmployeePhoto = $employee->profile_photo_path;
        $oldUserPhoto = $employee->user?->profile_photo_path;
        $newPath = $this->profilePhoto->store('profile-photos', 'public');

        $employee->profile_photo_path = $newPath;
        $employee->save();

        if ($employee->user) {
            $employee->user->profile_photo_path = $newPath;
            $employee->user->save();
        }

        foreach (array_unique(array_filter([$oldEmployeePhoto, $oldUserPhoto])) as $oldPhoto) {
            if ($oldPhoto !== $newPath && $oldPhoto !== 'profile-photos/.default-photo.jpg' && Storage::disk('public')->exists($oldPhoto)) {
                Storage::disk('public')->delete($oldPhoto);
            }
        }

        $this->reset('profilePhoto');
    }

    public function canManageEmployees(): bool
    {
        return Auth::user()?->can('manage employees') || Auth::user()?->hasRole('Admin');
    }

    public function canCreateEmployees(): bool
    {
        return $this->canManageEmployees() || Auth::user()?->can('create employees');
    }

    public function canEditEmployees(): bool
    {
        return $this->canManageEmployees() || Auth::user()?->can('edit employees');
    }

    public function canDeleteEmployees(): bool
    {
        return $this->canManageEmployees() || Auth::user()?->can('delete employees');
    }

    public function canAssignEmployeePermissions(): bool
    {
        return $this->canAssignPrimaryAdminRole() || Auth::user()?->can('manage employee roles') === true;
    }

    public function canAssignPrimaryAdminRole(): bool
    {
        $user = Auth::user();

        return (int) ($user?->id ?? 0) === 1 || (int) ($user?->employee_id ?? 0) === 1;
    }

    private function normalizePermissionInputs(): void
    {
        if ($this->canAssignEmployeePermissions()) {
            if (($this->employeeInfo['accessRole'] ?? 'Employee') === 'Admin' && ! $this->canAssignPrimaryAdminRole()) {
                $this->employeeInfo['accessRole'] = $this->isEdit
                    ? ($this->employee?->user?->roles->where('name', '!=', 'Admin')->pluck('name')->first() ?? 'Employee')
                    : 'Employee';
            }

            return;
        }

        $this->employeeInfo['accessRole'] = $this->isEdit
            ? ($this->employee?->user?->roles->pluck('name')->first() ?? 'Employee')
            : 'Employee';
    }

    private function ensureCanCreateEmployees(): void
    {
        if (! $this->canCreateEmployees()) {
            abort(403);
        }
    }

    private function ensureCanEditEmployees(): void
    {
        if (! $this->canEditEmployees()) {
            abort(403);
        }
    }

    private function ensureCanDeleteEmployees(): void
    {
        if (! $this->canDeleteEmployees()) {
            abort(403);
        }
    }
}
