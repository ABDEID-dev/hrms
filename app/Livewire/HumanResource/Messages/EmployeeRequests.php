<?php

namespace App\Livewire\HumanResource\Messages;

use App\Models\AccountTransaction;
use App\Models\Discount;
use App\Models\EmployeeRequest;
use App\Models\Message;
use App\Models\User;
use App\Notifications\DefaultNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeRequests extends Component
{
    use WithPagination;

    public $searchTerm = '';

    public $statusFilter = '';

    public $selectedRequestId = null;

    public $adminResponse = '';

    public $approvedAmount = null;

    public function mount(): void
    {
        $this->authorizeViewRequestsPage();

        $requestId = (int) request()->query('request');

        if ($requestId > 0) {
            $this->selectRequest($requestId);
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
        $requests = EmployeeRequest::with(['employee', 'complaintAgainstEmployee'])
            ->when(! $this->canManageAllRequests(), function ($query) {
                $query->where('employee_id', Auth::user()->employee_id);
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->searchTerm !== '', function ($query) {
                $query->where(function ($nested) {
                    $nested->where('title', 'like', '%'.$this->searchTerm.'%')
                        ->orWhere('type', 'like', '%'.$this->searchTerm.'%')
                        ->orWhere('status', 'like', '%'.$this->searchTerm.'%');

                    if (Schema::hasColumn('employee_requests', 'complainant_name')) {
                        $nested->orWhere('complainant_name', 'like', '%'.$this->searchTerm.'%');
                    }

                    if (Schema::hasColumn('employee_requests', 'complaint_against_other')) {
                        $nested->orWhere('complaint_against_other', 'like', '%'.$this->searchTerm.'%');
                    }

                    $nested->orWhereHas('employee', function ($employeeQuery) {
                        $employeeQuery->where('id', 'like', '%'.$this->searchTerm.'%')
                            ->orWhere('first_name', 'like', '%'.$this->searchTerm.'%');
                    });
                });
            })
            ->latest()
            ->paginate(12);

        return view('livewire.human-resource.messages.employee-requests', [
            'requests' => $requests,
        ]);
    }

    public function selectRequest(int $requestId): void
    {
        $this->authorizeRequestAccess($requestId);

        $request = EmployeeRequest::findOrFail($requestId);

        $this->selectedRequestId = $request->id;
        $this->adminResponse = $request->admin_response ?? '';
        $this->approvedAmount = $request->amount;
    }

    public function cancelReview(): void
    {
        $this->authorizeReviewRequests();

        $this->selectedRequestId = null;
        $this->adminResponse = '';
        $this->approvedAmount = null;
        $this->resetValidation();
    }

    public function approveRequest(int $requestId): void
    {
        abort_unless($this->canApproveRequests(), 403);

        if ($this->selectedRequestId !== $requestId) {
            $this->selectRequest($requestId);
        }

        $this->updateRequestStatus($requestId, 'approved');
    }

    public function rejectRequest(int $requestId): void
    {
        abort_unless($this->canRejectRequests(), 403);
        $this->updateRequestStatus($requestId, 'rejected');
    }

    public function cancelRequest(int $requestId): void
    {
        abort_unless($this->canCancelRequests(), 403);
        $this->updateRequestStatus($requestId, 'cancelled');
    }

    public function saveResponse(int $requestId): void
    {
        abort_unless($this->canSaveRequestResponse(), 403);

        $this->validate([
            'adminResponse' => 'required|string|min:2|max:3000',
        ]);

        $request = EmployeeRequest::with('employee.user')->findOrFail($requestId);
        $response = trim($this->adminResponse);
        $status = $request->status === 'pending' ? 'replied' : $request->status;

        if (! $this->applyRequestUpdate($request, $status, $response)) {
            $this->dispatch('toastr', type: 'info', message: __('ui.request_already_up_to_date'));

            return;
        }

        $this->notifyEmployeeAboutRequestUpdate(
            $request->fresh(['employee.user']),
            __('ui.request_replied_notification', ['title' => $request->title])
        );

        $this->selectedRequestId = $requestId;
        $this->adminResponse = $response;
        $this->dispatch('toastr', type: 'success', message: __('ui.request_response_saved'));
    }

    private function updateRequestStatus(int $requestId, string $status): void
    {
        $request = EmployeeRequest::with('employee.user')->findOrFail($requestId);
        $previousStatus = $request->status;

        if (
            $request->type === 'advance'
            && $previousStatus === 'approved'
            && $status === 'rejected'
            && (int) Auth::id() !== 1
        ) {
            $this->dispatch('toastr', type: 'error', message: 'حذف أو إلغاء السلفة المعتمدة متاح للمدير الرئيسي فقط.');

            return;
        }

        $approvedAmount = $this->resolveApprovedAdvanceAmount($request, $status);

        if ($approvedAmount === false) {
            return;
        }

        if (
            $request->type === 'advance'
            && $previousStatus === 'approved'
            && $status === 'approved'
            && $approvedAmount !== null
            && round((float) $request->amount, 2) !== round((float) $approvedAmount, 2)
            && (int) Auth::id() !== 1
        ) {
            $this->dispatch('toastr', type: 'error', message: 'تعديل السلفة المعتمدة متاح للمدير الرئيسي فقط.');

            return;
        }

        if ($approvedAmount !== null) {
            $request->amount = $approvedAmount;
        }

        $response = trim($this->adminResponse ?: (string) $request->admin_response);
        $amountWasChanged = $approvedAmount !== null
            && round((float) $request->getOriginal('amount'), 2) !== round($approvedAmount, 2);

        if ($amountWasChanged && $response === trim((string) $request->admin_response)) {
            $response = '';
        }

        if (! $this->applyRequestUpdate($request, $status, $response, $approvedAmount)) {
            $this->dispatch('toastr', type: 'info', message: __('ui.request_already_up_to_date'));

            return;
        }

        $request = $request->fresh(['employee.user']);
        $this->syncAdvanceSalaryDeduction($request, $previousStatus, $status);
        $this->syncAdvanceTreasuryExpense($request, $previousStatus, $status);

        $messageKey = $this->buildRequestStatusNotification($request, $status);

        $this->notifyEmployeeAboutRequestUpdate($request, $messageKey);
        $this->notifyAdvancePayoutUsers($request, $previousStatus, $status);

        $this->selectedRequestId = $requestId;
        $this->adminResponse = $response;
        $this->dispatch('toastr', type: 'success', message: __('ui.request_status_updated'));
    }

    private function applyRequestUpdate(EmployeeRequest $request, string $status, string $response, ?float $approvedAmount = null): bool
    {
        $normalizedResponse = $response !== '' ? $response : null;
        $hasChanges = $request->status !== $status || $request->admin_response !== $normalizedResponse;

        if ($approvedAmount !== null) {
            $hasChanges = $hasChanges || round((float) $request->getOriginal('amount'), 2) !== round($approvedAmount, 2);
        }

        if (! $hasChanges) {
            return false;
        }

        $data = [
            'admin_response' => $normalizedResponse,
            'status' => $status,
            'reviewed_by' => Auth::user()->name,
            'reviewed_at' => Carbon::now(),
        ];

        if ($approvedAmount !== null) {
            $data['amount'] = $approvedAmount;
        }

        $request->update($data);

        return true;
    }

    private function resolveApprovedAdvanceAmount(EmployeeRequest $request, string $status): float|false|null
    {
        if ($request->type !== 'advance' || $status !== 'approved') {
            return null;
        }

        $this->validate(
            [
                'approvedAmount' => ['required', 'numeric', 'min:1', 'max:'.(float) $request->amount],
            ],
            [],
            [
                'approvedAmount' => __('ui.approved_advance_amount'),
            ]
        );

        return round((float) $this->approvedAmount, 2);
    }

    private function syncAdvanceSalaryDeduction(EmployeeRequest $request, string $previousStatus, string $status): void
    {
        if ($request->type !== 'advance' || is_null($request->amount)) {
            return;
        }

        $reason = $this->advanceDiscountReason($request);

        if ($status === 'approved') {
            Discount::updateOrCreate(
                [
                    'employee_id' => $request->employee_id,
                    'reason' => $reason,
                ],
                [
                    'rate' => (int) round((float) $request->amount),
                    'date' => Carbon::today('Asia/Dubai')->toDateString(),
                    'is_auto' => false,
                    'is_sent' => false,
                    'batch' => Carbon::today('Asia/Dubai')->format('Y-m'),
                ]
            );

            return;
        }

        if ($previousStatus === 'approved' && in_array($status, ['rejected', 'cancelled'], true)) {
            Discount::query()
                ->where('employee_id', $request->employee_id)
                ->where('reason', $reason)
                ->delete();
        }
    }

    private function syncAdvanceTreasuryExpense(EmployeeRequest $request, string $previousStatus, string $status): void
    {
        if ($request->type !== 'advance' || is_null($request->amount)) {
            return;
        }

        $note = $this->advanceTreasuryNote($request);
        $account = $this->resolveAdvanceTreasuryAccount();

        if ($status === 'approved') {
            AccountTransaction::updateOrCreate(
                [
                    'account' => $account,
                    'type' => 'expense',
                    'expense_kind' => 'advance',
                    'note' => $note,
                ],
                [
                    'employee_id' => $request->employee_id,
                    'payroll_month' => Carbon::today('Asia/Dubai')->format('Y-m'),
                    'date' => Carbon::today('Asia/Dubai')->toDateString(),
                    'employee_name' => $request->employee?->full_name,
                    'service' => null,
                    'quantity' => 1,
                    'unit_price' => round((float) $request->amount, 2),
                    'amount' => round((float) $request->amount, 2),
                    'payment_method' => 'cash',
                    'has_invoice' => false,
                    'withdrawn_to' => $request->employee?->full_name,
                    'customer_name' => null,
                ]
            );

            return;
        }

        if ($previousStatus === 'approved' && in_array($status, ['rejected', 'cancelled'], true)) {
            AccountTransaction::query()
                ->where('type', 'expense')
                ->where('expense_kind', 'advance')
                ->where('note', $note)
                ->delete();
        }
    }

    private function advanceDiscountReason(EmployeeRequest $request): string
    {
        return __('ui.advance_salary_deduction_reason', ['id' => $request->id]);
    }

    private function advanceTreasuryNote(EmployeeRequest $request): string
    {
        return 'تم صرف سلفة بواسطة المحل كاش - طلب رقم '.$request->id;
    }

    private function resolveAdvanceTreasuryAccount(): string
    {
        return 'maktoom';
    }

    private function buildRequestStatusNotification(EmployeeRequest $request, string $status): string
    {
        if ($request->type === 'advance') {
            return match ($status) {
                'approved' => __('ui.advance_approved_notification', ['amount' => number_format((float) $request->amount, 2)]),
                'cancelled' => __('ui.advance_cancelled_notification', ['amount' => number_format((float) $request->amount, 2)]),
                default => __('ui.advance_rejected_notification', ['amount' => number_format((float) $request->amount, 2)]),
            };
        }

        return match ($status) {
            'approved' => __('ui.request_approved_notification', ['title' => $request->title]),
            'cancelled' => __('ui.request_cancelled_notification', ['title' => $request->title]),
            default => __('ui.request_rejected_notification', ['title' => $request->title]),
        };
    }

    private function notifyEmployeeAboutRequestUpdate(EmployeeRequest $request, string $message): void
    {
        $employeeUser = $request->employee?->user;

        if (! $employeeUser instanceof User) {
            return;
        }

        $icon = match ($request->status) {
            'approved' => 'ti ti-circle-check',
            'rejected' => 'ti ti-circle-x',
            'cancelled' => 'ti ti-ban',
            default => 'ti ti-message-2',
        };

        $employeeUser->notify(new DefaultNotification(
            Auth::id(),
            $message,
            route('employee-management-responses', ['request' => $request->id], false),
            $icon
        ));

        Message::create([
            'employee_id' => $request->employee->id,
            'text' => $this->buildEmployeeMessageBody($request, $message),
            'recipient' => $request->employee->full_phone_number ?: 'internal',
            'is_sent' => false,
        ]);
    }

    private function notifyAdvancePayoutUsers(EmployeeRequest $request, string $previousStatus, string $status): void
    {
        if ($request->type !== 'advance' || $status !== 'approved' || $previousStatus === 'approved') {
            return;
        }

        $message = __('ui.advance_payout_required_notification', [
            'name' => $request->employee?->full_name ?: '---',
            'amount' => number_format((float) $request->amount, 2),
        ]);

        User::query()
            ->where('id', '!=', Auth::id())
            ->where(function ($query) {
                $query
                    ->whereHas('roles', function ($roleQuery) {
                        $roleQuery
                            ->where('guard_name', 'web')
                            ->whereIn('name', ['Admin', 'ManagementEmployee', 'Accountant']);
                    })
                    ->orWhereHas('permissions', function ($permissionQuery) {
                        $permissionQuery
                            ->where('guard_name', 'web')
                            ->whereIn('name', ['view employee requests', 'view accounts treasury']);
                    });
            })
            ->get()
            ->filter(fn (User $user) => $user->canAccessAccountBranch($this->resolveAdvanceTreasuryAccount()))
            ->each(function (User $user) use ($message, $request) {
                $account = $this->resolveAdvanceTreasuryAccount();
                $url = $user->can('view employee requests')
                    ? route('messages-employee-requests', ['request' => $request->id], false)
                    : route('accounts-'.$account.'-treasury', absolute: false);

                $user->notify(new DefaultNotification(
                    Auth::id(),
                    $message,
                    $url,
                    'ti ti-cash'
                ));
            });
    }

    private function buildEmployeeMessageBody(EmployeeRequest $request, string $message): string
    {
        $lines = [
            $message,
            __('ui.request_type').': '.__('ui.request_type_'.$request->type),
        ];

        $formattedAmount = ! is_null($request->amount)
            ? number_format((float) $request->amount, 2)
            : null;

        if ($formattedAmount && ! str_contains($message, $formattedAmount)) {
            $lines[] = __('ui.amount').': '.$formattedAmount;
        }

        if ($request->admin_response && ! str_contains($message, trim($request->admin_response))) {
            $lines[] = __('ui.admin_response').': '.$request->admin_response;
        }

        return implode(PHP_EOL, $lines);
    }

    public function canManageAllRequests(): bool
    {
        return Auth::user()->can('view employee requests');
    }

    public function canReviewRequests(): bool
    {
        return $this->canSaveRequestResponse()
            || $this->canApproveRequests()
            || $this->canRejectRequests()
            || $this->canCancelRequests();
    }

    public function canSaveRequestResponse(): bool
    {
        return Auth::user()->can('review employee requests');
    }

    public function canApproveRequests(): bool
    {
        return Auth::user()->can('approve employee requests');
    }

    public function canRejectRequests(): bool
    {
        return Auth::user()->can('reject employee requests');
    }

    public function canCancelRequests(): bool
    {
        return Auth::user()->can('cancel employee requests');
    }

    private function authorizeReviewRequests(): void
    {
        abort_unless($this->canReviewRequests(), 403);
    }

    private function authorizeViewRequestsPage(): void
    {
        abort_unless(Auth::user()->can('view employee requests'), 403);
    }

    private function authorizeRequestAccess(int $requestId): void
    {
        if ($this->canManageAllRequests()) {
            return;
        }

        $requestBelongsToUser = EmployeeRequest::whereKey($requestId)
            ->where('employee_id', Auth::user()->employee_id)
            ->exists();

        abort_unless($requestBelongsToUser, 403);
    }
}
