<?php

namespace App\Livewire\Settings;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Users extends Component
{
    use WithPagination;

    public ?string $searchTerm = null;

    public ?string $roleFilter = null;

    public ?int $confirmedUserId = null;

    public ?int $editingUserRoleId = null;

    public array $roleEditForm = [
        'role' => '',
    ];

    public $availableRoles;

    public $availableEmployees;

    public array $userForm = [
        'employee_id' => '',
        'name' => '',
        'username' => '',
        'email' => '',
        'password' => '',
        'role' => 'Employee',
    ];

    public function mount(): void
    {
        $this->roleFilter = request()->query('role');
        $this->loadAvailableRoles();
        $this->loadAvailableEmployees();
    }

    public function render()
    {
        $users = User::query()
            ->with(['employee', 'roles'])
            ->when($this->roleFilter, function ($query) {
                $query->whereHas('roles', fn ($roleQuery) => $roleQuery->where('name', $this->roleFilter));
            })
            ->when($this->searchTerm, function ($query) {
                $search = '%'.$this->searchTerm.'%';

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', $search)
                        ->orWhere('username', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhereHas('employee', fn ($employeeQuery) => $employeeQuery->where('first_name', 'like', $search));
                });
            })
            ->latest('id')
            ->paginate(20);

        return view('livewire.settings.users', [
            'users' => $users,
            'availableRoles' => $this->availableRoles,
            'availableEmployees' => $this->availableEmployees,
        ]);
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function showEditUserRole(int $userId): void
    {
        $this->ensureCanManageEmployeeRoles();

        $user = User::query()->with('roles')->findOrFail($userId);

        abort_if($user->hasRole('Admin') && ! $this->canAssignPrimaryAdminRole(), 403);

        $this->editingUserRoleId = $user->id;
        $this->confirmedUserId = null;
        $this->roleEditForm['role'] = $user->roles->first()?->name ?: (string) $this->availableRoles->first()?->name;
    }

    public function cancelEditUserRole(): void
    {
        $this->editingUserRoleId = null;
        $this->roleEditForm = ['role' => ''];
    }

    public function updateUserRole(): void
    {
        $this->ensureCanManageEmployeeRoles();

        $this->validate([
            'roleEditForm.role' => ['required', Rule::exists('roles', 'name')],
        ]);

        $user = User::query()->with('roles')->findOrFail($this->editingUserRoleId);

        abort_if($user->hasRole('Admin') && ! $this->canAssignPrimaryAdminRole(), 403);

        $this->ensureRoleCanBeAssigned($this->roleEditForm['role'], 'roleEditForm.role');

        $user->syncRoles([$this->roleEditForm['role']]);

        $this->cancelEditUserRole();
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function createUser(): void
    {
        $this->userForm['name'] = trim((string) $this->userForm['name']);
        $this->userForm['username'] = trim((string) $this->userForm['username']);
        $this->userForm['email'] = trim((string) $this->userForm['email']);

        if (! $this->canManageEmployeeRoles()) {
            $this->userForm['role'] = 'Employee';
        }

        $this->validate([
            'userForm.employee_id' => ['required', 'integer', 'exists:employees,id', Rule::unique('users', 'employee_id')],
            'userForm.name' => ['required', 'string', 'max:255'],
            'userForm.username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')],
            'userForm.email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'userForm.password' => ['required', 'string', 'min:8'],
            'userForm.role' => ['required', Rule::exists('roles', 'name')],
        ]);

        $this->ensureRoleCanBeAssigned($this->userForm['role'], 'userForm.role');

        DB::transaction(function () {
            $employee = Employee::query()->findOrFail($this->userForm['employee_id']);

            $user = User::create([
                'name' => $this->userForm['name'] ?: $employee->full_name,
                'employee_id' => $employee->id,
                'mobile' => $employee->full_phone_number ?: null,
                'username' => $this->userForm['username'],
                'email' => $this->userForm['email'] ?: null,
                'password' => Hash::make($this->userForm['password']),
                'visible_password' => $this->userForm['password'],
                'profile_photo_path' => $employee->profile_photo_path ?: 'profile-photos/.default-photo.jpg',
            ]);

            $user->assignRole(Role::firstOrCreate([
                'name' => $this->userForm['role'],
                'guard_name' => 'web',
            ]));
        });

        $this->reset('userForm');
        $this->userForm = [
            'employee_id' => '',
            'name' => '',
            'username' => '',
            'email' => '',
            'password' => '',
            'role' => 'Employee',
        ];
        $this->loadAvailableEmployees();
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function updatedUserFormEmployeeId($employeeId): void
    {
        $employee = Employee::query()->find($employeeId);

        if (! $employee) {
            return;
        }

        $this->userForm['name'] = $employee->full_name;
        $this->userForm['username'] = $this->userForm['username'] ?: (string) $employee->id;
    }

    public function confirmDeleteUser(int $userId): void
    {
        abort_if($userId === 1, 403);

        $this->confirmedUserId = $userId;
    }

    public function deleteUser(): void
    {
        abort_if($this->confirmedUserId === 1, 403);

        $user = User::query()->findOrFail($this->confirmedUserId);
        $user->delete();

        $this->confirmedUserId = null;
        $this->loadAvailableEmployees();
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    private function loadAvailableRoles(): void
    {
        $this->availableRoles = Role::query()
            ->when(! $this->canAssignPrimaryAdminRole(), fn ($query) => $query->where('name', '!=', 'Admin'))
            ->orderBy('name')
            ->get();
    }

    private function loadAvailableEmployees(): void
    {
        $this->availableEmployees = Employee::query()
            ->whereDoesntHave('user')
            ->orderBy('id')
            ->get();
    }

    private function ensureRoleCanBeAssigned(string $roleName, string $field): void
    {
        if ($roleName !== 'Admin' || $this->canAssignPrimaryAdminRole()) {
            return;
        }

        throw ValidationException::withMessages([
            $field => 'صلاحية المدير الرئيسي Admin لا يمكن منحها إلا من الحساب الأساسي رقم 1.',
        ]);
    }

    public function canAssignPrimaryAdminRole(): bool
    {
        $user = Auth::user();

        return (int) ($user?->id ?? 0) === 1 || (int) ($user?->employee_id ?? 0) === 1;
    }

    public function canManageEmployeeRoles(): bool
    {
        return $this->canAssignPrimaryAdminRole() || Auth::user()?->can('manage employee roles') === true;
    }

    private function ensureCanManageEmployeeRoles(): void
    {
        abort_unless($this->canManageEmployeeRoles(), 403);
    }
}
