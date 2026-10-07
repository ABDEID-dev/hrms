<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::find(1);

        if (! $user) {
            return;
        }

        $adminRole = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        $user->assignRole($adminRole);
        $user->syncPermissions(Permission::query()->pluck('name')->all());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $user = User::find(1);

        if ($user) {
            $user->revokePermissionTo(Permission::query()->pluck('name')->all());
        }
    }
};
