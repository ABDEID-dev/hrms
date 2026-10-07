<?php

namespace App\Http\Controllers;

use App\Models\EmployeeLocationEvent;
use App\Models\Fingerprint;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class EmployeeLocationController extends Controller
{
    private const OFFICIAL_TIMEZONE = 'Asia/Dubai';

    private const ATTENDANCE_DAY_END_HOUR = 6;

    private const TRACKED_ADMIN_USER_IDS = [95];

    public function systemOpen(Request $request)
    {
        $user = Auth::user();

        if (
            ! $user
            || ! $this->canTrackUser($user)
        ) {
            return response()->noContent();
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
        ]);

        $now = Carbon::now(self::OFFICIAL_TIMEZONE);

        if ($this->hasEventTable()) {
            EmployeeLocationEvent::create([
                'user_id' => $user->id,
                'employee_id' => $user->employee_id,
                'event_type' => 'system_open',
                'latitude' => round((float) $validated['latitude'], 7),
                'longitude' => round((float) $validated['longitude'], 7),
                'accuracy' => isset($validated['accuracy']) ? round((float) $validated['accuracy'], 2) : null,
                'occurred_at' => $now,
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ]);
        }

        if ($user->employee_id && $this->hasLocationColumns()) {
            $fingerprint = Fingerprint::firstOrNew([
                'employee_id' => $user->employee_id,
                'date' => $this->currentAttendanceDate(),
            ]);

            if ($fingerprint->system_open_at) {
                return response()->noContent();
            }

            $fingerprint->fill([
                'system_open_at' => $now,
                'system_open_latitude' => round((float) $validated['latitude'], 7),
                'system_open_longitude' => round((float) $validated['longitude'], 7),
                'system_open_accuracy' => isset($validated['accuracy']) ? round((float) $validated['accuracy'], 2) : null,
            ])->save();
        }

        return response()->noContent();
    }

    private function canTrackUser($user): bool
    {
        if ((int) $user->id === 1) {
            return false;
        }

        if ($user->hasRole('Admin')) {
            return true;
        }

        if (in_array((int) $user->id, self::TRACKED_ADMIN_USER_IDS, true)) {
            return true;
        }

        return $user->employee_id
            && $user->hasAnyRole(['Employee', 'ManagementEmployee'])
            && ! $user->hasRole('Admin');
    }

    private function currentAttendanceDate(): string
    {
        $now = Carbon::now(self::OFFICIAL_TIMEZONE);

        if ($now->hour < self::ATTENDANCE_DAY_END_HOUR) {
            return $now->copy()->subDay()->toDateString();
        }

        return $now->toDateString();
    }

    private function hasLocationColumns(): bool
    {
        return Schema::hasColumn('fingerprints', 'system_open_latitude');
    }

    private function hasEventTable(): bool
    {
        return Schema::hasTable('employee_location_events');
    }
}
