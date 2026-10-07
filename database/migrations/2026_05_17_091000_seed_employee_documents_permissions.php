<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $permissions = [
            [
                'name' => 'view employee documents',
                'display_name' => 'عرض مستندات الموظفين',
                'group_name' => 'الموارد البشرية',
            ],
            [
                'name' => 'manage employee documents',
                'display_name' => 'إدارة مستندات الموظفين',
                'group_name' => 'الموارد البشرية',
            ],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                [
                    'name' => $permission['name'],
                    'guard_name' => 'web',
                ],
                [
                    'display_name' => $permission['display_name'],
                    'group_name' => $permission['group_name'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $roleIds = DB::table('roles')
            ->where('guard_name', 'web')
            ->whereIn('name', ['Admin', 'HR', 'ManagementEmployee'])
            ->pluck('id');

        $permissionIds = DB::table('permissions')
            ->where('guard_name', 'web')
            ->whereIn('name', array_column($permissions, 'name'))
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                DB::table('role_has_permissions')->updateOrInsert([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->where('guard_name', 'web')
            ->whereIn('name', ['view employee documents', 'manage employee documents'])
            ->pluck('id');

        if ($permissionIds->isEmpty()) {
            return;
        }

        DB::table('role_has_permissions')
            ->whereIn('permission_id', $permissionIds)
            ->delete();

        DB::table('permissions')
            ->whereIn('id', $permissionIds)
            ->delete();
    }
};
