<?php

namespace App\Livewire\Employee;

use App\Models\AdminAlert;
use App\Models\Employee;
use App\Models\EmployeeRequest;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class ManagementResponses extends Component
{
    public ?Employee $employee = null;

    public $managementAlert = null;

    public $requests;

    public $recentMessages;

    public $recentNotifications;

    public $unreadNotificationsCount = 0;

    public bool $isAdminView = false;

    public bool $showOnlyNotifications = false;

    public bool $showNotifications = false;

    public ?int $selectedRequestId = null;

    public $responseStats = [
        'approved' => 0,
        'replied' => 0,
        'pending' => 0,
    ];

    public function mount(): void
    {
        $user = Auth::user();

        $this->employee = $user->employee;
        $this->isAdminView = $user->hasRole('Admin');
        $this->showOnlyNotifications = ! $this->isAdminView && ! $user->can('view employee portal');

        abort_if(! $this->employee && ! $this->isAdminView && ! $this->showOnlyNotifications, 404, 'Employee profile not found.');

        $requestId = (int) request()->query('request');
        $this->selectedRequestId = $requestId > 0 ? $requestId : null;

        $this->loadResponses();
    }

    public function render()
    {
        $this->loadResponses();

        return view('livewire.employee.management-responses');
    }

    public function markNotificationAsRead(string $notificationId): void
    {
        $notification = Auth::user()->unreadNotifications()
            ->where('id', $notificationId)
            ->first();

        if ($notification && $this->canSeeNotification($notification)) {
            $notification->markAsRead();
            $this->loadResponses();
        }
    }

    public function markAllNotificationsAsRead(): void
    {
        Auth::user()->unreadNotifications
            ->filter(fn ($notification) => $this->canSeeNotification($notification))
            ->each
            ->markAsRead();

        $this->loadResponses();
    }

    public function toggleNotifications(): void
    {
        $this->showNotifications = ! $this->showNotifications;
    }

    public function cancelRequest(int $requestId): void
    {
        if (! $this->canHideEmployeeRequests()) {
            $this->dispatch('toastr', type: 'error', message: __('ui.run_migrations_first'));

            return;
        }

        $request = $this->ownRequest($requestId);

        if ($request->status !== 'pending') {
            $this->dispatch('toastr', type: 'error', message: __('ui.only_pending_requests_can_be_cancelled'));

            return;
        }

        $request->update([
            'status' => 'cancelled',
            'employee_hidden_at' => Carbon::now(),
        ]);

        $this->dispatch('toastr', type: 'success', message: __('ui.request_cancelled_and_removed'));
    }

    public function hideRequest(int $requestId): void
    {
        if (! $this->canHideEmployeeRequests()) {
            $this->dispatch('toastr', type: 'error', message: __('ui.run_migrations_first'));

            return;
        }

        $request = $this->ownRequest($requestId);

        $request->update([
            'employee_hidden_at' => Carbon::now(),
        ]);

        $this->dispatch('toastr', type: 'success', message: __('ui.request_removed_from_your_center'));
    }

    private function loadResponses(): void
    {
        if ($this->showOnlyNotifications) {
            $this->managementAlert = null;
            $this->requests = collect();
            $this->recentMessages = collect();
            $this->responseStats = [
                'approved' => 0,
                'replied' => 0,
                'pending' => 0,
            ];

            $this->recentNotifications = Auth::user()->notifications()
                ->latest()
                ->take(80)
                ->get()
                ->filter(fn ($notification) => $this->canSeeNotification($notification))
                ->take(20)
                ->values();

            $this->unreadNotificationsCount = Auth::user()->unreadNotifications
                ->filter(fn ($notification) => $this->canSeeNotification($notification))
                ->count();

            return;
        }

        $this->loadManagementAlert();

        $query = EmployeeRequest::query()
            ->with(['employee', 'complaintAgainstEmployee']);

        if (! $this->isAdminView) {
            $query->where('employee_id', Auth::user()->employee_id);

            if (Schema::hasColumn('employee_requests', 'employee_hidden_at')) {
                $query->whereNull('employee_hidden_at');
            }
        }

        $this->responseStats = [
            'approved' => (clone $query)->where('status', 'approved')->count(),
            'replied' => (clone $query)->where('status', 'replied')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
        ];

        if (Schema::hasTable('employee_request_documents')) {
            $query->with('visibleDocuments');
        }

        $this->requests = $query
            ->when($this->selectedRequestId, function ($query) {
                $query->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$this->selectedRequestId]);
            })
            ->orderByRaw('COALESCE(reviewed_at, created_at) DESC')
            ->latest('id')
            ->take($this->isAdminView ? 80 : 30)
            ->get();

        $messagesQuery = Message::query()->with('employee');

        if (! $this->isAdminView) {
            $messagesQuery->where('employee_id', Auth::user()->employee_id);
        }

        $this->recentMessages = $messagesQuery
            ->latest()
            ->take($this->isAdminView ? 60 : 12)
            ->get();

        $this->recentNotifications = Auth::user()->notifications()
            ->latest()
            ->take(12)
            ->get()
            ->filter(fn ($notification) => $this->canSeeNotification($notification))
            ->reject(fn ($notification) => $this->notificationAppearsInInternalMessages(
                trim((string) ($notification->data['message'] ?? ''))
            ))
            ->values();

        $this->unreadNotificationsCount = Auth::user()->unreadNotifications
            ->filter(fn ($notification) => $this->canSeeNotification($notification))
            ->count();
    }

    private function notificationAppearsInInternalMessages(string $notificationMessage): bool
    {
        if ($notificationMessage === '') {
            return false;
        }

        return collect($this->recentMessages)->contains(function ($message) use ($notificationMessage) {
            $internalMessage = is_string($message) ? $message : (string) ($message->text ?? '');
            $firstLine = trim(explode("\n", $internalMessage, 2)[0]);

            return $firstLine === $notificationMessage;
        });
    }

    private function loadManagementAlert(): void
    {
        if (! Schema::hasTable('admin_alerts')) {
            $this->managementAlert = null;

            return;
        }

        $alert = AdminAlert::query()->active()->latest()->first();

        $this->managementAlert = $alert ? [
            'body' => $alert->getLocalizedBody(),
            'updated_at' => $alert->updated_at,
        ] : null;
    }

    private function ownRequest(int $requestId): EmployeeRequest
    {
        abort_if($this->isAdminView || ! Auth::user()->employee_id, 403);

        return EmployeeRequest::query()
            ->where('employee_id', Auth::user()->employee_id)
            ->findOrFail($requestId);
    }

    private function canHideEmployeeRequests(): bool
    {
        return Schema::hasColumn('employee_requests', 'employee_hidden_at');
    }

    private function canSeeNotification($notification): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($this->isAdminView) {
            return true;
        }

        $data = $notification->data ?? [];
        $url = (string) ($data['url'] ?? '');

        if (($data['type'] ?? null) === 'personal_message' || isset($data['message_id'])) {
            return true;
        }

        $requestId = $this->notificationRequestId($url);

        if ($requestId) {
            return EmployeeRequest::query()
                ->where('id', $requestId)
                ->where('employee_id', $user->employee_id)
                ->exists();
        }

        return false;
    }

    private function notificationRequestId(string $url): ?int
    {
        if (! str_contains($url, 'employee/management-responses')
            && ! str_contains($url, 'employee-management-responses')) {
            return null;
        }

        $query = parse_url($url, PHP_URL_QUERY);

        if (! is_string($query)) {
            return null;
        }

        parse_str($query, $parameters);
        $requestId = (int) ($parameters['request'] ?? 0);

        return $requestId > 0 ? $requestId : null;
    }
}
