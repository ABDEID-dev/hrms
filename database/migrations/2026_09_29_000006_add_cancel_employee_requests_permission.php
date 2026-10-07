<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->updateOrInsert(
            ['name' => 'cancel employee requests', 'guard_name' => 'web'],
            [
                'display_name' => 'إلغاء طلبات الموظفين',
                'group_name' => 'الرسائل',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->grantPermissions('Admin', ['cancel employee requests']);
        $this->grantPermissions('HR', ['cancel employee requests']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')
            ->where('name', 'cancel employee requests')
            ->where('guard_name', 'web')
            ->value('id');

        if ($permissionId) {
            DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
            DB::table('model_has_permissions')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }

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
