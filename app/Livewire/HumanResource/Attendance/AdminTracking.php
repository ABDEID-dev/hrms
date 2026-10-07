<?php

namespace App\Livewire\HumanResource\Attendance;

use App\Models\EmployeeLocationEvent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class AdminTracking extends Component
{
    private const OFFICIAL_TIMEZONE = 'Asia/Dubai';

    private const TRACKED_ADMIN_USER_IDS = [95];

    public string $fromDate;

    public string $toDate;

    public $openEvents;

    public array $activityRows = [];

    public array $stats = [
        'opens' => 0,
        'actions' => 0,
        'updates' => 0,
        'deletes' => 0,
    ];

    public function mount(): void
    {
        abort_unless((int) Auth::id() === 1, 403);

        $today = Carbon::now(self::OFFICIAL_TIMEZONE);
        $this->fromDate = $today->copy()->startOfMonth()->toDateString();
        $this->toDate = $today->toDateString();
        $this->loadReport();
    }

    public function render()
    {
        return view('livewire.human-resource.attendance.admin-tracking');
    }

    public function updatedFromDate(): void
    {
        $this->normalizeDates();
        $this->loadReport();
    }

    public function updatedToDate(): void
    {
        $this->normalizeDates();
        $this->loadReport();
    }

    public function resetToToday(): void
    {
        $today = Carbon::now(self::OFFICIAL_TIMEZONE)->toDateString();
        $this->fromDate = $today;
        $this->toDate = $today;
        $this->loadReport();
    }

    public function resetToThisMonth(): void
    {
        $today = Carbon::now(self::OFFICIAL_TIMEZONE);
        $this->fromDate = $today->copy()->startOfMonth()->toDateString();
        $this->toDate = $today->toDateString();
        $this->loadReport();
    }

    public function refreshReport(): void
    {
        $this->loadReport();
    }

    private function loadReport(): void
    {
        $this->normalizeDates();

        $from = Carbon::parse($this->fromDate, self::OFFICIAL_TIMEZONE)->startOfDay();
        $to = Carbon::parse($this->toDate, self::OFFICIAL_TIMEZONE)->endOfDay();

        $trackedAdminUserIds = $this->trackedAdminUserIds();

        $this->openEvents = $this->hasEventTable()
            ? EmployeeLocationEvent::query()
                ->whereIn('user_id', $trackedAdminUserIds)
                ->where('event_type', 'system_open')
                ->whereBetween('occurred_at', [$from, $to])
                ->latest('occurred_at')
                ->take(500)
                ->get()
            : collect();

        $this->activityRows = $this->readActivityRows($from, $to, $trackedAdminUserIds)
            ->map(function (array $row) {
                $row['location'] = $this->nearestOpenLocation($row['logged_at']);

                return $row;
            })
            ->sortByDesc('logged_at')
            ->take(500)
            ->values()
            ->all();

        $activity = collect($this->activityRows);
        $this->stats = [
            'opens' => $this->openEvents->count(),
            'actions' => $activity->count(),
            'updates' => $activity->where('action', 'updated')->count(),
            'deletes' => $activity->where('action', 'deleted')->count(),
        ];
    }

    private function readActivityRows(Carbon $from, Carbon $to, array $trackedAdminUserIds): Collection
    {
        $rows = [];

        foreach ($this->activityLogFiles() as $file) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                $row = $this->parseActivityLine($line, $trackedAdminUserIds);

                if (! $row) {
                    continue;
                }

                $loggedAt = Carbon::parse($row['logged_at'], self::OFFICIAL_TIMEZONE);
                if ($loggedAt->betweenIncluded($from, $to)) {
                    $rows[] = $row;
                }
            }
        }

        return collect($rows);
    }

    private function parseActivityLine(string $line, array $trackedAdminUserIds): ?array
    {
        $line = trim($line);

        if (! preg_match('/^\[(?<date>.*?)\].*?:\s*(?<message>.*?)\s+(?<json>\{.*\})\s*$/u', $line, $matches)) {
            return null;
        }

        $context = json_decode($matches['json'], true);
        if (! is_array($context) || ! in_array((int) ($context['actor_id'] ?? 0), $trackedAdminUserIds, true)) {
            return null;
        }

        $action = (string) ($context['action'] ?? 'activity');
        $loggedAt = Carbon::parse($matches['date'])->timezone(self::OFFICIAL_TIMEZONE);

        return [
            'logged_at' => $loggedAt->format('Y-m-d H:i:s'),
            'action' => $action,
            'action_label' => $this->actionLabel($action),
            'message' => (string) ($matches['message'] ?? ''),
            'model' => $this->modelLabel((string) ($context['model'] ?? '')),
            'model_id' => $context['model_id'] ?? null,
            'method' => $context['method'] ?? null,
            'url' => $context['url'] ?? null,
            'ip' => $context['ip'] ?? null,
            'old' => $this->formatContextArray($context['old'] ?? []),
            'new' => $this->formatContextArray($context['new'] ?? ($context['attributes'] ?? [])),
        ];
    }

    private function nearestOpenLocation(string $loggedAt): ?array
    {
        $loggedAt = Carbon::parse($loggedAt, self::OFFICIAL_TIMEZONE);

        $event = collect($this->openEvents)
            ->first(function ($event) use ($loggedAt) {
                $occurredAt = Carbon::parse($event->occurred_at, self::OFFICIAL_TIMEZONE);

                return $occurredAt->lessThanOrEqualTo($loggedAt)
                    && $occurredAt->isSameDay($loggedAt);
            });

        if (! $event) {
            return null;
        }

        return [
            'latitude' => $event->latitude,
            'longitude' => $event->longitude,
            'accuracy' => $event->accuracy,
            'occurred_at' => Carbon::parse($event->occurred_at, self::OFFICIAL_TIMEZONE)->format('Y-m-d H:i:s'),
        ];
    }

    private function actionLabel(string $action): string
    {
        return match ($action) {
            'created' => 'إضافة',
            'updated' => 'تعديل',
            'deleted' => 'حذف',
            'restored' => 'استرجاع',
            'login' => 'دخول',
            'logout' => 'خروج',
            'permissions_synced' => 'صلاحيات',
            default => 'نشاط',
        };
    }

    private function modelLabel(string $model): string
    {
        if ($model === '') {
            return 'السيستم';
        }

        return class_basename($model);
    }

    private function formatContextArray(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->map(fn ($item) => is_scalar($item) || $item === null ? (string) $item : json_encode($item, JSON_UNESCAPED_UNICODE))
            ->take(12)
            ->all();
    }

    private function activityLogFiles(): array
    {
        $files = glob(storage_path('logs/activity*.log')) ?: [];

        return collect($files)
            ->unique()
            ->filter(fn (string $file) => File::exists($file))
            ->values()
            ->all();
    }

    private function trackedAdminUserIds(): array
    {
        $ids = self::TRACKED_ADMIN_USER_IDS;

        try {
            $ids = array_merge(
                $ids,
                User::role('Admin')
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all()
            );
        } catch (\Throwable) {
            return $ids;
        }

        return array_values(array_filter(
            array_unique(array_map('intval', $ids)),
            fn (int $id) => $id !== 1
        ));
    }

    private function normalizeDates(): void
    {
        $this->fromDate = $this->fromDate ?: Carbon::now(self::OFFICIAL_TIMEZONE)->startOfMonth()->toDateString();
        $this->toDate = $this->toDate ?: Carbon::now(self::OFFICIAL_TIMEZONE)->toDateString();

        if (Carbon::parse($this->fromDate)->greaterThan(Carbon::parse($this->toDate))) {
            [$this->fromDate, $this->toDate] = [$this->toDate, $this->fromDate];
        }
    }

    private function hasEventTable(): bool
    {
        return Schema::hasTable('employee_location_events');
    }
}
