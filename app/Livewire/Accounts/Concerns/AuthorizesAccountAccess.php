<?php

namespace App\Livewire\Accounts\Concerns;

use App\Support\AccountPermissions;
use Illuminate\Support\Facades\Auth;

trait AuthorizesAccountAccess
{
    private function authorizeAccountAccess(string $account): void
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        if (AccountPermissions::canView($user, $account)) {
            return;
        }

        abort(403);
    }
}
