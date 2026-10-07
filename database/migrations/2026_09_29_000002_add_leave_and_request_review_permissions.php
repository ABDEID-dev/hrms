<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permissions = [
        ['name' => 'manage attendance leaves', 'display_name' => 'إدارة الإجازات', 'group_name' => 'الحضور'],
        ['name' => 'create attendance leaves', 'display_name' => 'إضافة إجازة', 'group_name' => 'الحضور'],
        ['name' => 'edit attendance leaves', 'display_name' => 'تعديل إجازة', 'group_name' => 'الحضور'],
        ['name' => 'delete attendance leaves', 'display_name' => 'حذف إجازة', 'group_name' => 'الحضور'],
        ['name' => 'review employee requests', 'display_name' => 'كتابة رد على طلبات الموظفين', 'group_name' => 'الرسائل'],
        ['name' => 'approve employee requests', 'display_name' => 'الموافقة على طلبات الموظفين', 'group_name' => 'الرسائل'],
        ['name' => 'reject employee requests', 'display_name' => 'رفض طلبات الموظفين', 'group_name' => 'الرسائل'],
    ];

    public function up(): void
    {
        foreach ($this->permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name'], 'guard_name' => 'web'],
                [
                    'display_name' => $permission['display_name'],
                    'group_name' => $permission['group_name'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $this->grantPermissions('Admin', array_column($this->permissions, 'name'));
        $this->grantPermissions('HR', array_column($this->permissions, 'name'));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $permissionNames = array_column($this->permissions, 'name');
        $permissionIds = DB::table('permissions')
            ->whereIn('name', $permissionNames)
            ->where('guard_name', 'web')
            ->pluck('id');

        DB::table('role_has_permissions')
            ->whereIn('permission_id', $permissionIds)
            ->delete();

        DB::table('permissions')
            ->whereIn('id', $permissionIds)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function grantPermissions(string $roleName, array $permissionNames): void
    {
        $role = DB::table('roles')
            ->where('name', $roleName)
            ->where('guard_name', 'web')
            ->first();

        if (! $role) {
            return;
        }

        DB::table('permissions')
            ->whereIn('name', $permissionNames)
            ->where('guard_name', 'web')
            ->pluck('id')
            ->each(function ($permissionId) use ($role) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $permissionId,
                    'role_id' => $role->id,
                ]);
            });
    }
};
