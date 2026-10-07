<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $roleNames = [
        'AM',
        'Accountant',
        'AvaniSalonSales',
        'AvaniPerfumesSales',
        'CC',
        'CR',
        'Employss',
        'HR',
        'MaktoomSalonSales',
        'Viewer',
    ];

    public function up(): void
    {
        $roleIds = DB::table('roles')
            ->whereIn('name', $this->roleNames)
            ->pluck('id');

        if ($roleIds->isEmpty()) {
            return;
        }

        DB::table(config('permission.table_names.role_has_permissions', 'role_has_permissions'))
            ->whereIn('role_id', $roleIds)
            ->delete();

        DB::table(config('permission.table_names.model_has_roles', 'model_has_roles'))
            ->whereIn('role_id', $roleIds)
            ->delete();

        DB::table('roles')
            ->whereIn('id', $roleIds)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
