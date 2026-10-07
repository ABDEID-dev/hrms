<?php

namespace App\Providers;

use App\Models\AccountTransaction;
use App\Models\Asset;
use App\Models\BulkMessage;
use App\Models\Category;
use App\Models\Center;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Discount;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Models\EmployeeRequest;
use App\Models\Fingerprint;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\Message;
use App\Models\Position;
use App\Models\SalonInvoice;
use App\Models\SalonService;
use App\Models\Setting;
use App\Models\SubCategory;
use App\Models\Timeline;
use App\Models\Transition;
use App\Models\User;
use App\Observers\AuditModelObserver;
use App\Support\AuditLogger;
use App\Support\SystemPermissions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /* preventLazyLoading
         * preventAccessingMissingAttributes
         * preventSilentlyDiscardingAttributes
         * you can enable all of them at once by using the 'Model::shouldBeStrict()'
         */
        // Model::preventAccessingMissingAttributes();
        // Model::preventSilentlyDiscardingAttributes();

        Lang::handleMissingKeysUsing(function (string $key, array $replacements, string $locale) {
            info("Missing translation key [$key] detected.");

            return $key;
        });

        Carbon::setWeekStartsAt(Carbon::SUNDAY);

        Carbon::setWeekendDays([Carbon::FRIDAY, Carbon::SATURDAY]);

        $this->registerAuditLogging();
        $this->seedSystemPermissions();
    }

    private function registerAuditLogging(): void
    {
        foreach ([
            AccountTransaction::class,
            Asset::class,
            BulkMessage::class,
            Category::class,
            Center::class,
            Contract::class,
            Customer::class,
            Department::class,
            Discount::class,
            Employee::class,
            EmployeeLeave::class,
            EmployeeRequest::class,
            Fingerprint::class,
            Holiday::class,
            Leave::class,
            Message::class,
            Permission::class,
            Position::class,
            Role::class,
            SalonInvoice::class,
            SalonService::class,
            Setting::class,
            SubCategory::class,
            Timeline::class,
            Transition::class,
            User::class,
        ] as $modelClass) {
            if (! class_exists($modelClass)) {
                continue;
            }

            $modelClass::observe(AuditModelObserver::class);
        }

        Event::listen(Login::class, function (Login $event) {
            AuditLogger::log('login', 'User logged in', [
                'model' => $event->user::class,
                'model_id' => $event->user->getKey(),
                'employee_id' => $event->user->employee_id ?? null,
                'username' => $event->user->username ?? null,
            ]);
        });

        Event::listen(Logout::class, function (Logout $event) {
            if (! $event->user) {
                return;
            }

            AuditLogger::log('logout', 'User logged out', [
                'model' => $event->user::class,
                'model_id' => $event->user->getKey(),
                'employee_id' => $event->user->employee_id ?? null,
                'username' => $event->user->username ?? null,
            ]);
        });
    }

    private function seedSystemPermissions(): void
    {
        try {
            if (! Schema::hasTable('permissions') || ! Schema::hasTable('roles')) {
                return;
            }

            $signature = md5(json_encode([
                SystemPermissions::all(),
                SystemPermissions::roleDefaults(),
            ]));

            Cache::rememberForever('system-permissions-seeded-'.$signature, function () {
                foreach (SystemPermissions::all() as $permissionData) {
                    $permission = Permission::query()->firstOrCreate([
                        'name' => $permissionData['name'],
                        'guard_name' => 'web',
                    ]);

                    $permission->forceFill([
                        'display_name' => $permissionData['display_name'],
                        'group_name' => $permissionData['group_name'],
                    ])->save();
                }

                foreach (SystemPermissions::roleDefaults() as $roleName => $permissions) {
                    $role = Role::query()->firstOrCreate([
                        'name' => $roleName,
                        'guard_name' => 'web',
                    ]);

                    $role->givePermissionTo(
                        Permission::query()
                            ->whereIn('name', $permissions)
                            ->pluck('name')
                            ->all()
                    );
                }

                app(PermissionRegistrar::class)->forgetCachedPermissions();

                return true;
            });
        } catch (\Throwable) {
            return;
        }
    }
}
