<?php

namespace App\Livewire\Settings;

use App\Support\SystemPermissions;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class Permissions extends Component
{
    public Collection $permissions;

    public ?string $searchTerm = null;

    public ?int $confirmedPermissionId = null;

    public ?int $editingPermissionId = null;

    public array $permissionForm = [
        'name' => '',
        'display_name' => '',
        'group_name' => '',
    ];

    public function mount(): void
    {
        $this->ensureCanManagePermissions();

        $this->seedDefaultPermissions();
        $this->loadPermissions();
    }

    public function render()
    {
        return view('livewire.settings.permissions');
    }

    public function updatedSearchTerm(): void
    {
        $this->loadPermissions();
    }

    public function savePermission(): void
    {
        $this->ensureCanManagePermissions();

        $this->permissionForm['name'] = trim((string) $this->permissionForm['name']);
        $this->permissionForm['display_name'] = trim((string) $this->permissionForm['display_name']);
        $this->permissionForm['group_name'] = trim((string) $this->permissionForm['group_name']);

        $this->validate([
            'permissionForm.name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')->ignore($this->editingPermissionId),
            ],
            'permissionForm.display_name' => ['nullable', 'string', 'max:255'],
            'permissionForm.group_name' => ['nullable', 'string', 'max:255'],
        ]);

        $permission = $this->editingPermissionId
            ? Permission::query()->findOrFail($this->editingPermissionId)
            : new Permission();

        $permission->forceFill([
            'name' => $this->permissionForm['name'],
            'guard_name' => 'web',
            'display_name' => $this->permissionForm['display_name'] ?: $this->permissionForm['name'],
            'group_name' => $this->permissionForm['group_name'] ?: __('General'),
        ]);
        $permission->save();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->resetPermissionForm();
        $this->loadPermissions();
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function syncSystemPermissions(): void
    {
        $this->ensureCanManagePermissions();

        $this->seedDefaultPermissions();
        $this->loadPermissions();
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function editPermission(int $permissionId): void
    {
        $this->ensureCanManagePermissions();

        $permission = Permission::query()->findOrFail($permissionId);

        $this->editingPermissionId = $permission->id;
        $this->confirmedPermissionId = null;
        $this->permissionForm = [
            'name' => $permission->name,
            'display_name' => $permission->display_name ?: '',
            'group_name' => $permission->group_name ?: '',
        ];
    }

    public function cancelEdit(): void
    {
        $this->resetPermissionForm();
    }

    public function confirmDeletePermission(int $permissionId): void
    {
        $this->ensureCanManagePermissions();

        $permission = Permission::query()->findOrFail($permissionId);

        abort_if($this->isSystemPermissionName($permission->name), 403);

        $this->confirmedPermissionId = $permissionId;
    }

    public function deletePermission(): void
    {
        $this->ensureCanManagePermissions();

        $permission = Permission::query()->findOrFail($this->confirmedPermissionId);

        abort_if($this->isSystemPermissionName($permission->name), 403);

        $permission->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->confirmedPermissionId = null;
        $this->editingPermissionId = null;
        $this->loadPermissions();
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    private function resetPermissionForm(): void
    {
        $this->editingPermissionId = null;
        $this->confirmedPermissionId = null;
        $this->permissionForm = ['name' => '', 'display_name' => '', 'group_name' => ''];
    }

    private function ensureCanManagePermissions(): void
    {
        abort_unless(auth()->user()?->can('manage permissions'), 403);
    }

    private function loadPermissions(): void
    {
        $this->permissions = Permission::query()
            ->when($this->searchTerm, function ($query) {
                $search = '%'.$this->searchTerm.'%';

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', $search)
                        ->orWhere('display_name', 'like', $search)
                        ->orWhere('group_name', 'like', $search);
                });
            })
            ->orderBy('group_name')
            ->orderBy('display_name')
            ->orderBy('name')
            ->get()
            ->each(function (Permission $permission) {
                $permission->setAttribute('is_system', $this->isSystemPermissionName($permission->name));
            });
    }

    private function seedDefaultPermissions(): void
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

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function isSystemPermissionName(string $permissionName): bool
    {
        return in_array($permissionName, $this->systemPermissionNames(), true);
    }

    private function systemPermissionNames(): array
    {
        return array_column(SystemPermissions::all(), 'name');
    }
}
