<?php

namespace App\Livewire\Sections\Navbar;

use App\Models\Import;
use App\Models\EmployeeRequest;
use App\Models\User;
use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class Navbar extends Component
{
    // Variables - Start //
    public $unreadNotifications;

    public $activeProgressBar = false;

    public $percentage = 0;

    public $imports;

    public $isMaintenance = 0;
    // Variables - End //

    public function render()
    {
        $this->isMaintenance = App::isDownForMaintenance();

        DB::table('failed_jobs')->truncate();
        auth()->user()
          ? ($this->unreadNotifications = $this->visibleUnreadNotifications())
          : ($this->unreadNotifications = []);

        return view('livewire.sections.navbar.navbar');
    }

    #[On('refreshNotifications')]
    public function refresh()
    {
        $this->unreadNotifications = $this->visibleUnreadNotifications();
    }

    #[On('activeProgressBar')]
    public function updateProgressBar()
    {
        $failedJobs = app(FailedJobProviderInterface::class)->all();

        if (! $failedJobs) {
            $this->activeProgressBar = true;

            $import_data = Import::latest()->first();
            if ($import_data->status == 'processing') {
                if ($import_data->total > 0) {
                    $this->percentage = round($import_data->current / ($import_data->total / 100));
                }
            } else {
                session()->flash('success', __('Imported Successfully!'));
                $this->percentage = 100;
                $this->activeProgressBar = false;
            }
        } else {
            session()->flash('error', 'Error Occurred, '.count($failedJobs).' Job Failed, Check Log File!');
            $this->activeProgressBar = false;
        }
    }

    public function markNotificationAsRead($notificationId)
    {
        abort_unless(Auth::user()?->can('view notifications'), 403);

        $notification = Auth::user()
            ->unreadNotifications->where('id', $notificationId)
            ->first();
        if ($notification && $this->canSeeNotification($notification)) {
            $notification->markAsRead();
        }
        $this->dispatch('refreshNotifications')->self();
    }

    public function markAllNotificationsAsRead()
    {
        abort_unless(Auth::user()?->can('view notifications'), 403);

        $user = User::find(auth()->user()->id);

        foreach ($user->unreadNotifications->filter(fn ($notification) => $this->canSeeNotification($notification)) as $notification) {
            $notification->markAsRead();
        }
    }

    public function turnMaintenanceModeOff()
    {
        if (App::isDownForMaintenance() == 1) {
            Artisan::call('up');
            Log::info('Maintenance mode has been suspended.');

            return redirect()->to('/');
        }
    }

    public function turnMaintenanceModeOn()
    {
        if (App::isDownForMaintenance() != 1) {
            Artisan::call('down');
            Log::info('Maintenance mode turned on.');

            return redirect()->to('/');
        }
    }

    private function visibleUnreadNotifications()
    {
        return auth()->user()
            ? auth()->user()->unreadNotifications->filter(fn ($notification) => $this->canSeeNotification($notification))->values()
            : collect();
    }

    private function canSeeNotification($notification): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if (! $user->can('view notifications')) {
            return false;
        }

        if (! $this->shouldRestrictEmployeeNotifications()) {
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

    private function shouldRestrictEmployeeNotifications(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return true;
        }

        if ($user->can('view employee requests')) {
            return false;
        }

        return true;
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
