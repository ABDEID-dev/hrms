<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('users') || ! DB::getSchemaBuilder()->hasTable('permissions')) {
            return;
        }

        foreach (User::query()->whereNotNull('account_access')->get() as $user) {
            $permissions = collect($user->account_accesses)
                ->map(fn (string $account) => 'view accounts '.$account)
                ->filter(fn (string $permission) => Permission::query()->where('name', $permission)->exists())
                ->values()
                ->all();

            if ($permissions !== []) {
                $user->givePermissionTo($permissions);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        //
    }
};
