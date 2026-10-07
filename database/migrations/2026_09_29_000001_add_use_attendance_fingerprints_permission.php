<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $permissionId = DB::table('permissions')->insertOrIgnore([
            'name' => 'use attendance fingerprints',
            'guard_name' => 'web',
            'display_name' => 'استخدام بصمة الدخول والخروج',
            'group_name' => 'الحضور',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $permission = DB::table('permissions')
            ->where('name', 'use attendance fingerprints')
            ->where('guard_name', 'web')
            ->first();

        if (! $permission) {
            return;
        }

        DB::table('roles')
            ->whereIn('name', ['Employee', 'ManagementEmployee'])
            ->where('guard_name', 'web')
            ->pluck('id')
            ->each(function ($roleId) use ($permission) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $permission->id,
                    'role_id' => $roleId,
                ]);
            });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $permission = DB::table('permissions')
            ->where('name', 'use attendance fingerprints')
            ->where('guard_name', 'web')
            ->first();

        if (! $permission) {
            return;
        }

        DB::table('role_has_permissions')
            ->where('permission_id', $permission->id)
            ->delete();

        DB::table('permissions')
            ->where('id', $permission->id)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
