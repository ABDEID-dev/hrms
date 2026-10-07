<?php

namespace App\Models;

use App\Traits\CreatedUpdatedDeletedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminAlert extends Model
{
    use CreatedUpdatedDeletedBy, HasFactory, SoftDeletes;

    protected $fillable = [
        'body_ar',
        'body_en',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getLocalizedBody(?string $locale = null): string
    {
        $locale = (string) ($locale ?: app()->getLocale());
        $isArabic = str_starts_with($locale, 'ar');

        if ($isArabic) {
            return (string) ($this->body_ar ?: $this->body_en ?: '');
        }

        return (string) ($this->body_en ?: $this->body_ar ?: '');
    }
}
