<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $permissionId = DB::table('permissions')
            ->where('name', 'create backdated account revenues')
            ->where('guard_name', 'web')
            ->value('id');

        if (! $permissionId) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => 'create backdated account revenues',
                'guard_name' => 'web',
                'display_name' => 'إضافة إيراد بتاريخ سابق',
                'group_name' => 'الحسابات',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('permissions')
                ->where('id', $permissionId)
                ->update([
                    'display_name' => 'إضافة إيراد بتاريخ سابق',
                    'group_name' => 'الحسابات',
                    'updated_at' => $now,
                ]);
        }

        $roleIds = DB::table('roles')
            ->whereIn('name', ['Admin', 'ManagementEmployee'])
            ->where('guard_name', 'web')
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $permissionId,
                'role_id' => $roleId,
            ]);
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')
            ->where('name', 'create backdated account revenues')
            ->where('guard_name', 'web')
            ->value('id');

        if (! $permissionId) {
            return;
        }

        DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
        DB::table('model_has_permissions')->where('permission_id', $permissionId)->delete();
        DB::table('permissions')->where('id', $permissionId)->delete();
    }
};
