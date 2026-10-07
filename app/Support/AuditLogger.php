<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    private const HIDDEN_FIELDS = [
        'password',
        'visible_password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'profile_photo_path',
    ];

    private const IGNORED_UPDATE_FIELDS = [
        'updated_at',
        'last_activity',
        'last_login',
    ];

    public static function log(string $action, string $message, array $context = []): void
    {
        $user = Auth::user();

        Log::channel('activity')->info($message, array_merge([
            'action' => $action,
            'actor_id' => $user?->id,
            'actor_name' => $user?->name,
            'actor_username' => $user?->username,
            'actor_employee_id' => $user?->employee_id,
            'ip' => request()?->ip(),
            'url' => request()?->fullUrl(),
            'method' => request()?->method(),
        ], $context));
    }

    public static function modelCreated(Model $model): void
    {
        self::log('created', self::label($model).' created', [
            'model' => $model::class,
            'model_id' => $model->getKey(),
            'attributes' => self::sanitize($model->getAttributes()),
        ]);
    }

    public static function modelUpdated(Model $model): void
    {
        $changes = self::sanitize($model->getChanges());

        foreach (self::IGNORED_UPDATE_FIELDS as $field) {
            unset($changes[$field]);
        }

        if ($changes === []) {
            return;
        }

        $old = [];
        foreach (array_keys($changes) as $field) {
            $old[$field] = $model->getOriginal($field);
        }

        self::log('updated', self::label($model).' updated', [
            'model' => $model::class,
            'model_id' => $model->getKey(),
            'old' => self::sanitize($old),
            'new' => $changes,
        ]);
    }

    public static function modelDeleted(Model $model): void
    {
        self::log('deleted', self::label($model).' deleted', [
            'model' => $model::class,
            'model_id' => $model->getKey(),
            'attributes' => self::sanitize($model->getAttributes()),
        ]);
    }

    public static function permissionsSynced(Model $user, array $permissions): void
    {
        self::log('permissions_synced', 'Employee direct permissions updated', [
            'model' => $user::class,
            'model_id' => $user->getKey(),
            'employee_id' => $user->employee_id ?? null,
            'username' => $user->username ?? null,
            'permissions' => $permissions,
        ]);
    }

    private static function label(Model $model): string
    {
        $shortName = class_basename($model);

        return match ($shortName) {
            'AccountTransaction' => 'Account transaction',
            'Employee' => 'Employee',
            'User' => 'User account',
            'Permission' => 'Permission',
            'Role' => 'Role',
            default => $shortName,
        };
    }

    private static function sanitize(array $data): array
    {
        foreach (self::HIDDEN_FIELDS as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = '[hidden]';
            }
        }

        return $data;
    }
}
