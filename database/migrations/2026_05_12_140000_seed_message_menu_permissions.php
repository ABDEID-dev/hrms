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
                'name' => 'view employee requests',
                'display_name' => 'عرض طلبات الموظفين',
                'group_name' => 'الرسائل',
            ],
            [
                'name' => 'manage management complaints',
                'display_name' => 'إدارة الشكاوى',
                'group_name' => 'الرسائل',
            ],
            [
                'name' => 'view deleted documents',
                'display_name' => 'عرض المستندات المحذوفة',
                'group_name' => 'الرسائل',
            ],
        ];

        foreach ($permissions as $permission) {
            $permissionId = DB::table('permissions')
                ->where('name', $permission['name'])
                ->where('guard_name', 'web')
                ->value('id');

            if (! $permissionId) {
                $permissionId = DB::table('permissions')->insertGetId([
                    'name' => $permission['name'],
                    'guard_name' => 'web',
                    'display_name' => $permission['display_name'],
                    'group_name' => $permission['group_name'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $managementRoleId = DB::table('roles')
                ->where('name', 'ManagementEmployee')
                ->where('guard_name', 'web')
                ->value('id');

            if ($managementRoleId) {
                DB::table('role_has_permissions')->updateOrInsert([
                    'permission_id' => $permissionId,
                    'role_id' => $managementRoleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->whereIn('name', [
                'view employee requests',
                'manage management complaints',
                'view deleted documents',
            ])
            ->pluck('id');

        DB::table('role_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
