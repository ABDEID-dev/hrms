<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use CreatedUpdatedDeletedBy,
        HasApiTokens,
        HasFactory,
        HasProfilePhoto,
        HasRoles,
        Notifiable,
        SoftDeletes,
        TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'employee_id',
        'mobile',
        'mobile_verified_at',
        'username',
        'email',
        'email_verified_at',
        'password',
        'visible_password',
        'account_access',
        'profile_photo_path',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_recovery_codes', 'two_factor_secret'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['profile_photo_url'];

    public static function accountBranches(): array
    {
        return [
            'maktoom' => 'مكتوم',
            'avani' => 'افاني',
            'perfumes' => 'العطور',
        ];
    }

    // 👉 Links
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // 👉 Attributes
    public function getEmployeeFullNameAttribute()
    {
        if ($this->employee) {
            return $this->employee->full_name;
        }

        return '';
    }

    public function getAccountAccessesAttribute(): array
    {
        if ($this->hasRole('Admin')) {
            return array_keys(self::accountBranches());
        }

        return collect(array_keys(self::accountBranches()))
            ->filter(fn (string $account) => $this->can('view accounts '.$account))
            ->values()
            ->all();
    }

    public function getAccountAccessLabelsAttribute(): string
    {
        $branches = self::accountBranches();
        $labels = collect($this->account_accesses)
            ->map(fn (string $branch) => $branches[$branch] ?? $branch)
            ->filter()
            ->values();

        return $labels->isEmpty() ? __('No access') : $labels->implode(', ');
    }

    public function canAccessAccountBranch(string $account): bool
    {
        if ($this->hasRole('Admin')) {
            return true;
        }

        return $this->can('view accounts '.$account);
    }

    public static function normalizeAccountAccess(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : [$value];
        }

        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->flatten()
            ->filter(fn ($branch) => is_string($branch) && array_key_exists($branch, self::accountBranches()))
            ->unique()
            ->values()
            ->all();
    }

    public static function serializeAccountAccess(mixed $value): ?string
    {
        $accesses = self::normalizeAccountAccess($value);

        return $accesses === [] ? null : json_encode($accesses);
    }
}
