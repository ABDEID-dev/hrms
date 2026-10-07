<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Implicitly grant "Admin" role all permission checks using can()
        Gate::before(function ($user, $ability) {
            $primaryAdminOnlyAbilities = [
                'view settings roles',
                'view settings permissions',
                'manage permissions',
                'manage employee roles',
            ];

            if (in_array($ability, $primaryAdminOnlyAbilities, true)) {
                return ((int) ($user->id ?? 0) === 1 || (int) ($user->employee_id ?? 0) === 1)
                    ? true
                    : null;
            }

            if ($user->hasRole('Admin')) {
                return true;
            }
        });
    }
}
