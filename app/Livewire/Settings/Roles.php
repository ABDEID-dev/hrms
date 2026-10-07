<?php

namespace App\Livewire\Settings;

use App\Support\SystemPermissions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class Roles extends Component
{
    public Collection $roles;

    public Collection $permissions;

    public array $projectPermissionPresets = [];

    public ?int $confirmedRoleId = null;

    public ?int $blockedRoleId = null;

    public ?int $editingRoleId = null;

    public array $roleForm = [
        'name' => '',
        'display_name' => '',
        'description' => '',
        'permissions' => [],
    ];

    private array $defaultRoles = [
        ['name' => 'Admin', 'display_name' => 'مدير', 'description' => 'صلاحية كاملة على النظام.'],
        ['name' => 'ManagementEmployee', 'display_name' => 'موظف إداري', 'description' => 'موظف إدارة يمكن منحه صلاحيات حسب القسم.'],
        ['name' => 'Employee', 'display_name' => 'موظف عادي', 'description' => 'بوابة الموظف والطلبات الشخصية.'],
    ];

    public function mount(): void
    {
        $this->ensureCanManagePermissions();

        $this->seedDefaultRoles();
        $this->projectPermissionPresets = SystemPermissions::projectPermissionPresets();
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.settings.roles');
    }

    public function showCreateRoleModal(): void
    {
        $this->ensureCanManagePermissions();

        $this->resetRoleForm();
        $this->dispatch('openModal', elementId: '#roleFormModal');
    }

    public function saveRole(): void
    {
        $this->ensureCanManagePermissions();

        $this->roleForm['name'] = trim((string) $this->roleForm['name']);
        $this->roleForm['display_name'] = trim((string) $this->roleForm['display_name']);
        $this->roleForm['description'] = trim((string) $this->roleForm['description']);
        $this->roleForm['permissions'] = array_values(array_unique($this->roleForm['permissions'] ?? []));

        $this->validate([
            'roleForm.name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($this->editingRoleId),
            ],
            'roleForm.display_name' => ['nullable', 'string', 'max:255'],
            'roleForm.description' => ['nullable', 'string', 'max:1000'],
            'roleForm.permissions' => ['nullable', 'array'],
            'roleForm.permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ]);

        $role = $this->editingRoleId
            ? Role::query()->findOrFail($this->editingRoleId)
            : new Role(['guard_name' => 'web']);

        if (($role->exists && $role->name === 'Admin' && ! $this->canManagePrimaryAdminRole())
            || ($this->roleForm['name'] === 'Admin' && ! $this->canManagePrimaryAdminRole())) {
            throw ValidationException::withMessages([
                'roleForm.name' => 'دور المدير الرئيسي Admin لا يمكن تعديله أو إنشاؤه إلا من الحساب الأساسي رقم 1.',
            ]);
        }

        $role->name = $this->roleForm['name'];
        $role->guard_name = 'web';

        if (Schema::hasColumn('roles', 'display_name')) {
            $role->display_name = $this->roleForm['display_name'] ?: $this->roleForm['name'];
        }

        if (Schema::hasColumn('roles', 'description')) {
            $role->description = $this->roleForm['description'] ?: null;
        }

        $role->save();
        $role->syncPermissions($this->roleForm['permissions']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->resetRoleForm();
        $this->loadData();
        $this->dispatch('closeModal', elementId: '#roleFormModal');
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function editRole(int $roleId): void
    {
        $this->ensureCanManagePermissions();

        $role = Role::query()->with('permissions')->findOrFail($roleId);

        abort_if($role->name === 'Admin' && ! $this->canManagePrimaryAdminRole(), 403);

        $this->editingRoleId = $role->id;
        $this->confirmedRoleId = null;
        $this->roleForm = [
            'name' => $role->name,
            'display_name' => Schema::hasColumn('roles', 'display_name') ? (string) ($role->display_name ?: '') : '',
            'description' => Schema::hasColumn('roles', 'description') ? (string) ($role->description ?: '') : '',
            'permissions' => $role->permissions->pluck('name')->values()->all(),
        ];

        $this->dispatch('openModal', elementId: '#roleFormModal');
    }

    public function canManagePrimaryAdminRole(): bool
    {
        $user = Auth::user();

        return (int) ($user?->id ?? 0) === 1 || (int) ($user?->employee_id ?? 0) === 1;
    }

    public function cancelEdit(): void
    {
        $this->resetRoleForm();
        $this->dispatch('closeModal', elementId: '#roleFormModal');
    }

    public function applyProjectPreset(string $presetKey): void
    {
        $this->ensureCanManagePermissions();

        $preset = $this->projectPermissionPresets[$presetKey] ?? null;

        if (! $preset) {
            return;
        }

        $this->roleForm['permissions'] = array_values(array_unique(array_merge(
            $this->roleForm['permissions'] ?? [],
            $preset['permissions'] ?? [],
        )));
    }

    public function removeProjectPreset(string $presetKey): void
    {
        $this->ensureCanManagePermissions();

        $preset = $this->projectPermissionPresets[$presetKey] ?? null;

        if (! $preset) {
            return;
        }

        $this->roleForm['permissions'] = array_values(array_diff(
            $this->roleForm['permissions'] ?? [],
            $preset['permissions'] ?? [],
        ));
    }

    public function selectPermissionGroup(string $groupName): void
    {
        $this->ensureCanManagePermissions();

        $permissions = $this->permissions
            ->where('group_name', $groupName)
            ->pluck('name')
            ->all();

        $this->roleForm['permissions'] = array_values(array_unique(array_merge(
            $this->roleForm['permissions'] ?? [],
            $permissions,
        )));
    }

    public function clearPermissionGroup(string $groupName): void
    {
        $this->ensureCanManagePermissions();

        $permissions = $this->permissions
            ->where('group_name', $groupName)
            ->pluck('name')
            ->all();

        $this->roleForm['permissions'] = array_values(array_diff(
            $this->roleForm['permissions'] ?? [],
            $permissions,
        ));
    }

    public function confirmDeleteRole(int $roleId): void
    {
        $this->ensureCanManagePermissions();

        if ($this->countRoleUsers($roleId) > 0) {
            $this->confirmedRoleId = null;
            $this->blockedRoleId = $roleId;

            return;
        }

        $this->blockedRoleId = null;
        $this->confirmedRoleId = $roleId;
    }

    public function deleteRole(): void
    {
        $this->ensureCanManagePermissions();

        $role = Role::query()->findOrFail($this->confirmedRoleId);

        abort_if(in_array($role->name, ['Admin', 'Employee'], true), 403);

        if ($this->countRoleUsers($role->id) > 0) {
            $this->blockedRoleId = $role->id;
            $this->confirmedRoleId = null;

            return;
        }

        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->confirmedRoleId = null;
        $this->blockedRoleId = null;
        $this->loadData();
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    private function loadData(): void
    {
        $this->roles = Role::query()
            ->with('permissions')
            ->orderBy('name')
            ->get()
            ->each(function (Role $role) {
                $role->setAttribute('users_count', $this->countRoleUsers($role->id));
            });

        $this->permissions = Permission::query()
            ->orderBy('group_name')
            ->orderBy('display_name')
            ->orderBy('name')
            ->get();
    }

    private function countRoleUsers(int $roleId): int
    {
        return DB::table(config('permission.table_names.model_has_roles', 'model_has_roles'))
            ->where('role_id', $roleId)
            ->where('model_type', config('auth.providers.users.model', 'App\\Models\\User'))
            ->count();
    }

    private function resetRoleForm(): void
    {
        $this->editingRoleId = null;
        $this->confirmedRoleId = null;
        $this->blockedRoleId = null;
        $this->roleForm = [
            'name' => '',
            'display_name' => '',
            'description' => '',
            'permissions' => [],
        ];
    }

    private function ensureCanManagePermissions(): void
    {
        abort_unless(auth()->user()?->can('manage permissions'), 403);
    }

    private function seedDefaultRoles(): void
    {
        $this->seedMissingPermissions();
        $defaultPermissionsByRole = SystemPermissions::roleDefaults();

        foreach ($this->defaultRoles as $roleData) {
            $role = Role::query()->firstOrCreate([
                'name' => $roleData['name'],
                'guard_name' => 'web',
            ]);
            $shouldSeedRolePermissions = $role->wasRecentlyCreated || $role->permissions()->count() === 0;

            if (Schema::hasColumn('roles', 'display_name')) {
                $role->display_name = $role->display_name ?: $roleData['display_name'];
            }

            if (Schema::hasColumn('roles', 'description')) {
                $role->description = $role->description ?: $roleData['description'];
            }

            $role->save();

            $permissions = Permission::query()
                ->whereIn('name', $defaultPermissionsByRole[$roleData['name']] ?? [])
                ->pluck('name')
                ->all();

            if ($shouldSeedRolePermissions && $permissions !== []) {
                $role->givePermissionTo($permissions);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function seedMissingPermissions(): void
    {
        foreach (SystemPermissions::all() as $permissionData) {
            $permission = Permission::query()->firstOrCreate([
                'name' => $permissionData['name'],
                'guard_name' => 'web',
            ]);

            $permission->forceFill([
                'display_name' => $permissionData['display_name'],
                'group_name' => $permissionData['group_name'],
            ])->save();
        }
    }
}
