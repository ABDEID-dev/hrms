<?php

namespace App\Livewire\HumanResource\Messages;

use App\Models\EmployeeRequestDocument;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class DeletedDocuments extends Component
{
    use WithPagination;

    public $searchTerm = '';

    public function mount(): void
    {
        $this->authorizeDeletedDocumentsAccess();
    }

    public function updatingSearchTerm(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $documents = EmployeeRequestDocument::with(['request.employee', 'employee'])
            ->whereNotNull('removed_from_complaint_at')
            ->whereNull('deleted_from_system_at')
            ->when($this->searchTerm !== '', function ($query) {
                $query->where(function ($nested) {
                    $nested->where('original_name', 'like', '%'.$this->searchTerm.'%')
                        ->orWhere('path', 'like', '%'.$this->searchTerm.'%')
                        ->orWhereHas('request', fn ($requestQuery) => $requestQuery->where('title', 'like', '%'.$this->searchTerm.'%'))
                        ->orWhereHas('employee', function ($employeeQuery) {
                            $employeeQuery->where('id', 'like', '%'.$this->searchTerm.'%')
                                ->orWhere('first_name', 'like', '%'.$this->searchTerm.'%')
                                ->orWhere('last_name', 'like', '%'.$this->searchTerm.'%');
                        });
                });
            })
            ->latest('removed_from_complaint_at')
            ->paginate(12);

        return view('livewire.human-resource.messages.deleted-documents', [
            'documents' => $documents,
        ]);
    }

    public function restoreDocument(int $documentId): void
    {
        $this->authorizeDeletedDocumentsAccess();

        $document = EmployeeRequestDocument::whereNull('deleted_from_system_at')->findOrFail($documentId);

        $document->update([
            'removed_from_complaint_at' => null,
            'removed_from_complaint_by' => null,
        ]);

        $this->dispatch('toastr', type: 'success', message: __('ui.document_restored_to_complaint'));
    }

    public function deleteDocumentFromSystem(int $documentId): void
    {
        $this->authorizeDeletedDocumentsAccess();

        $document = EmployeeRequestDocument::findOrFail($documentId);

        Storage::disk('public')->delete($document->path);

        $document->update([
            'deleted_from_system_at' => Carbon::now(),
            'deleted_from_system_by' => Auth::user()->name,
        ]);

        $this->dispatch('toastr', type: 'success', message: __('ui.document_deleted_from_system'));
    }

    private function authorizeDeletedDocumentsAccess(): void
    {
        abort_unless(Auth::user()->hasAnyRole(['Admin', 'HR']) || Auth::user()->can('view deleted documents'), 403);
    }
}
