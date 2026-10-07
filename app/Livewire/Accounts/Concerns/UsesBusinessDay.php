<?php

namespace App\Livewire\Accounts\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

trait UsesBusinessDay
{
    private const BUSINESS_DAY_START_HOUR = 11;

    private const BUSINESS_DAY_END_HOUR = 5;

    private const BUSINESS_TIMEZONE = 'Asia/Dubai';

    public function getCurrentBusinessDate(): string
    {
        $now = Carbon::now(self::BUSINESS_TIMEZONE);

        if ($now->hour < self::BUSINESS_DAY_END_HOUR) {
            return $now->copy()->subDay()->toDateString();
        }

        return $now->toDateString();
    }

    public function isBusinessWindowOpen(): bool
    {
        $hour = Carbon::now(self::BUSINESS_TIMEZONE)->hour;

        return $hour >= self::BUSINESS_DAY_START_HOUR || $hour < self::BUSINESS_DAY_END_HOUR;
    }

    public function canModifyBusinessDate(?string $date): bool
    {
        return $this->isAccountAdmin() || ($this->isBusinessWindowOpen() && $date === $this->getCurrentBusinessDate());
    }

    public function canDeleteBusinessDate(?string $date): bool
    {
        return $this->isAccountAdmin() || $this->canModifyBusinessDate($date);
    }

    private function isAccountAdmin(): bool
    {
        return (bool) Auth::user()?->hasRole('Admin');
    }

    private function businessWindowIsClosed(): bool
    {
        if ($this->isBusinessWindowOpen()) {
            return false;
        }

        $this->dispatch('toastr', type: 'error', message: __('accounts.business_day_closed'));

        return true;
    }

    private function abortIfRecordLocked(?string $date): bool
    {
        if ($this->canModifyBusinessDate($date)) {
            return false;
        }

        $this->dispatch('toastr', type: 'error', message: __('accounts.record_locked'));

        return true;
    }

    private function abortIfDeleteLocked(?string $date): bool
    {
        if ($this->canDeleteBusinessDate($date)) {
            return false;
        }

        $this->dispatch('toastr', type: 'error', message: __('accounts.record_locked'));

        return true;
    }
}
