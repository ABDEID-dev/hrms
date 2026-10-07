<?php

namespace App\Livewire\HumanResource\Structure;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class EmployeeDocuments extends Component
{
    use WithFileUploads, WithPagination;

    public $searchTerm = '';

    public ?int $selectedEmployeeId = null;

    public $documentType = EmployeeDocument::TYPE_IDENTITY;

    public $documentTitle = '';

    public $documentFile;

    public ?int $confirmedDocumentId = null;

    public ?int $previewDocumentId = null;

    protected $paginationTheme = 'bootstrap';

    public function mount(): void
    {
        $this->selectedEmployeeId = Employee::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->value('id');
    }

    public function updatedSearchTerm(): void
    {
        $this->resetPage();
    }

    public function selectEmployee(int $employeeId): void
    {
        $this->selectedEmployeeId = $employeeId;
        $this->confirmedDocumentId = null;
        $this->previewDocumentId = null;
        $this->resetValidation();
        $this->reset('documentFile', 'documentTitle');
    }

    public function previewDocument(int $documentId): void
    {
        if (! $this->documentsTableExists()) {
            return;
        }

        EmployeeDocument::query()
            ->where('employee_id', $this->selectedEmployeeId)
            ->findOrFail($documentId);

        $this->previewDocumentId = $documentId;
        $this->dispatch('showDocumentPreview');
    }

    public function closeDocumentPreview(): void
    {
        $this->previewDocumentId = null;
    }

    public function uploadDocument(): void
    {
        if (! Auth::user()?->can('manage employee documents')) {
            abort(403);
        }

        if (! $this->documentsTableExists()) {
            $this->dispatch('toastr', type: 'error', message: __('Please run database migrations first.'));

            return;
        }

        $this->validate([
            'selectedEmployeeId' => ['required', 'exists:employees,id'],
            'documentType' => ['required', 'in:'.implode(',', array_keys(EmployeeDocument::types()))],
            'documentTitle' => ['nullable', 'string', 'max:255'],
            'documentFile' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        if (! $this->documentFile instanceof TemporaryUploadedFile) {
            return;
        }

        $employee = Employee::findOrFail($this->selectedEmployeeId);
        $folder = 'employee-documents/'.$employee->id.'/'.$this->documentType;
        $path = $this->documentFile->store($folder, 'public');

        EmployeeDocument::create([
            'employee_id' => $employee->id,
            'type' => $this->documentType,
            'title' => trim((string) $this->documentTitle) ?: EmployeeDocument::types()[$this->documentType],
            'path' => $path,
            'original_name' => $this->documentFile->getClientOriginalName(),
            'mime' => $this->documentFile->getMimeType(),
            'size' => $this->documentFile->getSize(),
            'uploaded_by' => Auth::id(),
        ]);

        $this->reset('documentFile', 'documentTitle');
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function confirmDeleteDocument(int $documentId): void
    {
        if (! Auth::user()?->hasRole('Admin')) {
            abort(403);
        }

        $this->confirmedDocumentId = $documentId;
    }

    public function deleteDocument(EmployeeDocument $document): void
    {
        if (! Auth::user()?->hasRole('Admin')) {
            abort(403);
        }

        if (! $this->documentsTableExists()) {
            $this->dispatch('toastr', type: 'error', message: __('Please run database migrations first.'));

            return;
        }

        Storage::disk('public')->delete($document->path);
        $document->delete();
        $this->confirmedDocumentId = null;

        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }

    public function render()
    {
        $documentsTableExists = $this->documentsTableExists();

        $employeesQuery = Employee::query()
            ->when($this->searchTerm !== '', function ($query) {
                $search = '%'.$this->searchTerm.'%';

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('id', 'like', $search)
                        ->orWhere('first_name', 'like', $search)
                        ->orWhere('last_name', 'like', $search)
                        ->orWhere('national_number', 'like', $search)
                        ->orWhere('mobile_number', 'like', $search);
                });
            })
            ->orderBy('first_name')
            ->orderBy('last_name');

        if ($documentsTableExists) {
            $employeesQuery->withCount('documents');
        }

        $employees = $employeesQuery->paginate(12);

        $selectedEmployee = $this->selectedEmployeeId && $documentsTableExists
            ? Employee::with([
                'documents' => fn ($query) => $query->with('uploader')->latest(),
            ])->find($this->selectedEmployeeId)
            : ($this->selectedEmployeeId ? Employee::find($this->selectedEmployeeId) : null);
        $previewDocument = $this->previewDocumentId && $documentsTableExists
            ? EmployeeDocument::query()
                ->where('employee_id', $this->selectedEmployeeId)
                ->find($this->previewDocumentId)
            : null;

        return view('livewire.human-resource.structure.employee-documents', [
            'employees' => $employees,
            'selectedEmployee' => $selectedEmployee,
            'previewDocument' => $previewDocument,
            'documentTypes' => EmployeeDocument::types(),
            'documentsTableExists' => $documentsTableExists,
        ]);
    }

    private function documentsTableExists(): bool
    {
        return Schema::hasTable('employee_documents');
    }
}
