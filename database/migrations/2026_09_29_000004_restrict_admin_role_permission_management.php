<?php

use App\Support\SystemPermissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $adminRoleId = DB::table('roles')
            ->where('name', 'Admin')
            ->where('guard_name', 'web')
            ->value('id');

        if (! $adminRoleId) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', SystemPermissions::primaryAdminControlledPermissions())
            ->pluck('id');

        DB::table(config('permission.table_names.role_has_permissions', 'role_has_permissions'))
            ->where('role_id', $adminRoleId)
            ->whereIn('permission_id', $permissionIds)
            ->delete();
    }

    public function down(): void
    {
        $adminRoleId = DB::table('roles')
            ->where('name', 'Admin')
            ->where('guard_name', 'web')
            ->value('id');

        if (! $adminRoleId) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', SystemPermissions::primaryAdminControlledPermissions())
            ->pluck('id');

        foreach ($permissionIds as $permissionId) {
            DB::table(config('permission.table_names.role_has_permissions', 'role_has_permissions'))
                ->insertOrIgnore([
                    'permission_id' => $permissionId,
                    'role_id' => $adminRoleId,
                ]);
        }
    }
};
