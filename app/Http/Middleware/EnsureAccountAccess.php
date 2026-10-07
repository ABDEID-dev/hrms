<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAccountAccess
{
    public function handle(Request $request, Closure $next, string $account = null)
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $account = $account ?: $request->route('account');

        if (! is_string($account) || ! array_key_exists($account, User::accountBranches())) {
            abort(404);
        }

        if (! $user->canAccessAccountBranch($account)) {
            abort(403);
        }

        return $next($request);
    }
}
