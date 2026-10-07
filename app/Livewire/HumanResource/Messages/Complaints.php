<?php

namespace App\Livewire\HumanResource\Messages;

use App\Models\EmployeeRequest;
use App\Models\EmployeeRequestDocument;
use App\Models\Message;
use App\Models\User;
use App\Notifications\DefaultNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class Complaints extends Component
{
    use WithPagination;

    public $searchTerm = '';

    public $statusFilter = '';

    public $selectedComplaintId = null;

    public $adminResponse = '';

    public function mount(): void
    {
        $this->authorizeComplaintsAccess();

        $complaintId = (int) request()->query('complaint');

        if ($complaintId > 0) {
            $this->selectComplaint($complaintId);
        }
    }

    public function updatingSearchTerm(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $relations = ['employee', 'complaintAgainstEmployee'];

        if (Schema::hasTable('employee_request_documents')) {
            $relations[] = 'visibleDocuments';
        }

        $complaints = EmployeeRequest::with($relations)
            ->where('type', 'complaint')
            ->when($this->selectedComplaintId, function ($query) {
                $query->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$this->selectedComplaintId]);
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->searchTerm !== '', function ($query) {
                $query->where(function ($nested) {
                    $nested->where('title', 'like', '%'.$this->searchTerm.'%')
                        ->orWhere('body', 'like', '%'.$this->searchTerm.'%');

                    if (Schema::hasColumn('employee_requests', 'complainant_name')) {
                        $nested->orWhere('complainant_name', 'like', '%'.$this->searchTerm.'%');
                    }

                    if (Schema::hasColumn('employee_requests', 'complaint_against_other')) {
                        $nested->orWhere('complaint_against_other', 'like', '%'.$this->searchTerm.'%');
                    }

                    $nested->orWhereHas('employee', function ($employeeQuery) {
                            $employeeQuery->where('id', 'like', '%'.$this->searchTerm.'%')
                                ->orWhere('first_name', 'like', '%'.$this->searchTerm.'%')
                                ->orWhere('last_name', 'like', '%'.$this->searchTerm.'%');
                        })
                        ->orWhereHas('complaintAgainstEmployee', function ($employeeQuery) {
                            $employeeQuery->where('id', 'like', '%'.$this->searchTerm.'%')
                                ->orWhere('first_name', 'like', '%'.$this->searchTerm.'%')
                                ->orWhere('last_name', 'like', '%'.$this->searchTerm.'%');
                        });
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.human-resource.messages.complaints', [
            'complaints' => $complaints,
        ]);
    }

    public function selectComplaint(int $complaintId): void
    {
        $complaint = EmployeeRequest::where('type', 'complaint')->findOrFail($complaintId);

        $this->selectedComplaintId = $complaint->id;
        $this->adminResponse = $complaint->admin_response ?? '';
    }

    public function approveComplaint(int $complaintId): void
    {
        $this->authorizeComplaintsAccess();

        $this->updateComplaintStatus($complaintId, 'approved');
    }

    public function rejectComplaint(int $complaintId): void
    {
        $this->authorizeComplaintsAccess();

        $this->updateComplaintStatus($complaintId, 'rejected');
    }

    public function removeDocumentFromComplaint(int $documentId): void
    {
        $this->authorizeComplaintsAccess();

        $document = EmployeeRequestDocument::whereNull('deleted_from_system_at')->findOrFail($documentId);

        $document->update([
            'removed_from_complaint_at' => Carbon::now(),
            'removed_from_complaint_by' => Auth::user()->name,
        ]);

        $this->dispatch('toastr', type: 'success', message: __('ui.document_removed_from_complaint'));
    }

    public function saveResponse(int $complaintId): void
    {
        $this->authorizeComplaintsAccess();

        $this->validate([
            'adminResponse' => 'required|string|min:2|max:3000',
        ]);

        $complaint = EmployeeRequest::with('employee.user')
            ->where('type', 'complaint')
            ->findOrFail($complaintId);

        $response = trim($this->adminResponse);
        $status = $complaint->status === 'pending' ? 'replied' : $complaint->status;

        $this->applyComplaintUpdate($complaint, $status, $response);
        $this->notifyEmployee($complaint->fresh(['employee.user']), __('ui.complaint_replied_notification', ['title' => $complaint->title]));

        $this->dispatch('toastr', type: 'success', message: __('ui.request_response_saved'));
    }

    private function updateComplaintStatus(int $complaintId, string $status): void
    {
        $complaint = EmployeeRequest::with('employee.user')
            ->where('type', 'complaint')
            ->findOrFail($complaintId);

        $response = trim($this->adminResponse ?: (string) $complaint->admin_response);

        $this->applyComplaintUpdate($complaint, $status, $response);

        $message = $status === 'approved'
            ? __('ui.complaint_approved_notification', ['title' => $complaint->title])
            : __('ui.complaint_rejected_notification', ['title' => $complaint->title]);

        $this->notifyEmployee($complaint->fresh(['employee.user']), $message);
        $this->dispatch('toastr', type: 'success', message: __('ui.request_status_updated'));
    }

    private function applyComplaintUpdate(EmployeeRequest $complaint, string $status, string $response): void
    {
        $complaint->update([
            'admin_response' => $response !== '' ? $response : null,
            'status' => $status,
            'reviewed_by' => Auth::user()->name,
            'reviewed_at' => Carbon::now(),
        ]);

        $this->selectedComplaintId = $complaint->id;
        $this->adminResponse = $response;
    }

    private function notifyEmployee(EmployeeRequest $complaint, string $message): void
    {
        $employeeUser = $complaint->employee?->user;

        if (! $employeeUser instanceof User) {
            return;
        }

        $employeeUser->notify(new DefaultNotification(
            Auth::id(),
            $message,
            route('employee-management-responses', ['request' => $complaint->id], false),
            'ti ti-message-check'
        ));

        Message::create([
            'employee_id' => $complaint->employee->id,
            'text' => $message.PHP_EOL.__('ui.current_status').': '.__('ui.'.$complaint->status),
            'recipient' => $complaint->employee->full_phone_number ?: 'internal',
            'is_sent' => false,
        ]);
    }

    private function authorizeComplaintsAccess(): void
    {
        abort_unless(Auth::user()->hasAnyRole(['Admin', 'HR']) || Auth::user()->can('manage management complaints'), 403);
    }
}
