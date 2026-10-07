<?php

namespace App\Livewire\Employee;

use App\Models\Employee;
use App\Models\EmployeeRequest;
use App\Models\EmployeeRequestDocument;
use App\Models\User;
use App\Notifications\DefaultNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;

class Complaints extends Component
{
    use WithFileUploads;

    public ?Employee $employee = null;

    public $employees = [];

    public $complainantName = '';

    public $complaintTitle = '';

    public $complaintAgainst = '';

    public $complaintAgainstOther = '';

    public $complaintBody = '';

    public $attachment;

    public $voiceRecording;

    public $voiceRecordings = [];

    public $recentComplaints = [];

    public $extraDocuments = [];

    public function mount(): void
    {
        $this->employee = Auth::user()->employee;

        abort_if(! $this->employee, 404, 'Employee profile not found.');

        $this->complainantName = $this->employee->full_name;
        $this->loadComplaints();
    }

    public function render()
    {
        $this->employees = Employee::query()
            ->where('is_active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $this->loadComplaints();

        return view('livewire.employee.complaints');
    }

    public function submitComplaint(): void
    {
        $this->validate([
            'complainantName' => 'required|string|min:2|max:255',
            'complaintTitle' => 'required|string|min:3|max:255',
            'complaintAgainst' => 'required|string',
            'complaintAgainstOther' => 'required_if:complaintAgainst,other|nullable|string|min:2|max:255',
            'complaintBody' => 'required|string|min:10|max:5000',
            'attachment' => 'nullable|file|max:51200|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,txt,mp3,wav,m4a,ogg,webm,mp4,mov,avi,mkv',
            'voiceRecordings' => 'nullable|array|max:10',
            'voiceRecordings.*' => 'file|max:12288|mimetypes:audio/mpeg,audio/mp3,audio/webm,audio/ogg,video/webm',
        ]);

        $attachmentPath = null;
        $attachmentOriginalName = null;
        $attachmentMime = null;
        $voicePath = null;
        $voiceMime = null;
        $voiceDocuments = [];

        if ($this->attachment instanceof TemporaryUploadedFile) {
            $attachmentPath = $this->attachment->store('employee-complaints/attachments', 'public');
            $attachmentOriginalName = $this->attachment->getClientOriginalName();
            $attachmentMime = $this->attachment->getMimeType();
        }

        foreach ($this->voiceRecordings as $index => $voiceRecording) {
            if (! $voiceRecording instanceof TemporaryUploadedFile) {
                continue;
            }

            $currentVoiceMime = $voiceRecording->getMimeType();
            $voiceExtension = $this->safeAudioExtension($voiceRecording, $currentVoiceMime);
            $currentVoicePath = $voiceRecording->storeAs(
                'employee-complaints/voice',
                uniqid('complaint-voice-', true).'.'.$voiceExtension,
                'public'
            );

            $voiceDocuments[] = [
                'path' => $currentVoicePath,
                'mime' => $currentVoiceMime,
                'file' => $voiceRecording,
                'name' => __('ui.voice_recording').' '.($index + 1),
            ];

            if ($voicePath === null) {
                $voicePath = $currentVoicePath;
                $voiceMime = $currentVoiceMime;
            }
        }

        $complainantName = trim($this->complainantName);
        $complaintAgainstName = $this->complaintAgainst === 'other'
            ? trim($this->complaintAgainstOther)
            : (Employee::find((int) $this->complaintAgainst)?->full_name ?: '---');

        $body = trim($this->complaintBody);
        $details = [];

        if (! Schema::hasColumn('employee_requests', 'complainant_name')) {
            $details[] = __('ui.complainant_name').': '.$complainantName;
            $details[] = __('ui.complaint_against').': '.$complaintAgainstName;
        }

        if ($attachmentPath && ! Schema::hasColumn('employee_requests', 'attachment_path')) {
            $details[] = __('ui.attachment_or_media').': '.$attachmentPath;
        }

        if ($voiceDocuments !== [] && ! Schema::hasColumn('employee_requests', 'voice_path')) {
            foreach ($voiceDocuments as $voiceDocument) {
                $details[] = __('ui.voice_recording').': '.$voiceDocument['path'];
            }
        }

        if ($details !== []) {
            $body = implode(PHP_EOL, $details).PHP_EOL.PHP_EOL.$body;
        }

        $complaintData = [
            'employee_id' => Auth::user()->employee_id,
            'type' => 'complaint',
            'title' => trim($this->complaintTitle),
            'body' => $body,
            'status' => 'pending',
        ];

        if (Schema::hasColumn('employee_requests', 'complainant_name')) {
            $complaintData['complainant_name'] = $complainantName;
        }

        if (Schema::hasColumn('employee_requests', 'complaint_against_employee_id')) {
            $complaintData['complaint_against_employee_id'] = is_numeric($this->complaintAgainst) ? (int) $this->complaintAgainst : null;
        }

        if (Schema::hasColumn('employee_requests', 'complaint_against_other')) {
            $complaintData['complaint_against_other'] = $this->complaintAgainst === 'other' ? $complaintAgainstName : null;
        }

        if (Schema::hasColumn('employee_requests', 'attachment_path')) {
            $complaintData['attachment_path'] = $attachmentPath;
            $complaintData['attachment_original_name'] = $attachmentOriginalName;
            $complaintData['attachment_mime'] = $attachmentMime;
        }

        if (Schema::hasColumn('employee_requests', 'voice_path')) {
            $complaintData['voice_path'] = $voicePath;
            $complaintData['voice_mime'] = $voiceMime;
        }

        $request = EmployeeRequest::create($complaintData);

        $this->notifyManagement(
            __('ui.complaint_from_employee', ['name' => $complainantName]).' - '.$request->title,
            route('management-complaints', ['complaint' => $request->id], false)
        );
        $this->archiveComplaintDocument($request, $attachmentPath, $attachmentOriginalName, $attachmentMime, $this->attachment, 'attachment');
        foreach ($voiceDocuments as $voiceDocument) {
            $this->archiveComplaintDocument($request, $voiceDocument['path'], $voiceDocument['name'], $voiceDocument['mime'], $voiceDocument['file'], 'voice');
        }

        $this->reset('complaintTitle', 'complaintAgainst', 'complaintAgainstOther', 'complaintBody', 'attachment', 'voiceRecording', 'voiceRecordings');
        $this->complainantName = $this->employee->full_name;
        $this->loadComplaints();

        $this->dispatch('complaintSaved');
        $this->dispatch('toastr', type: 'success', message: __('ui.complaint_sent_successfully'));
    }

    private function loadComplaints(): void
    {
        $relations = ['complaintAgainstEmployee'];

        if (Schema::hasTable('employee_request_documents')) {
            $relations[] = 'visibleDocuments';
        }

        $this->recentComplaints = EmployeeRequest::with($relations)
            ->where('employee_id', Auth::user()->employee_id)
            ->where('type', 'complaint')
            ->latest()
            ->take(6)
            ->get();
    }

    public function removeDocumentFromComplaint(int $documentId): void
    {
        $document = EmployeeRequestDocument::where('employee_id', Auth::user()->employee_id)
            ->whereNull('deleted_from_system_at')
            ->findOrFail($documentId);

        $document->update([
            'removed_from_complaint_at' => now(),
            'removed_from_complaint_by' => Auth::user()->name,
        ]);

        $this->loadComplaints();
        $this->dispatch('toastr', type: 'success', message: __('ui.document_removed_from_complaint'));
    }

    public function addDocumentsToComplaint(int $complaintId): void
    {
        $this->validate([
            "extraDocuments.$complaintId" => 'required|array|max:10',
            "extraDocuments.$complaintId.*" => 'file|max:51200|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,txt,mp3,wav,m4a,ogg,webm,mp4,mov,avi,mkv',
        ]);

        if (! Schema::hasTable('employee_request_documents')) {
            return;
        }

        $request = EmployeeRequest::where('employee_id', Auth::user()->employee_id)
            ->where('type', 'complaint')
            ->findOrFail($complaintId);

        foreach (($this->extraDocuments[$complaintId] ?? []) as $document) {
            if (! $document instanceof TemporaryUploadedFile) {
                continue;
            }

            $mime = $document->getMimeType();
            $kind = str_starts_with((string) $mime, 'audio/') ? 'voice' : 'attachment';
            $folder = $kind === 'voice' ? 'employee-complaints/voice' : 'employee-complaints/attachments';
            $path = $kind === 'voice'
                ? $document->storeAs($folder, uniqid('complaint-voice-', true).'.'.$this->safeAudioExtension($document, $mime), 'public')
                : $document->store($folder, 'public');

            $this->archiveComplaintDocument($request, $path, $document->getClientOriginalName(), $mime, $document, $kind);
        }

        unset($this->extraDocuments[$complaintId]);

        $this->loadComplaints();
        $this->dispatch('toastr', type: 'success', message: __('ui.document_added_to_complaint'));
    }

    private function archiveComplaintDocument(
        EmployeeRequest $request,
        ?string $path,
        ?string $originalName,
        ?string $mime,
        $uploadedFile,
        string $kind
    ): void {
        if (! $path || ! Schema::hasTable('employee_request_documents')) {
            return;
        }

        EmployeeRequestDocument::create([
            'employee_request_id' => $request->id,
            'employee_id' => Auth::user()->employee_id,
            'kind' => $kind,
            'path' => $path,
            'original_name' => $originalName,
            'mime' => $mime,
            'size' => $uploadedFile instanceof TemporaryUploadedFile ? $uploadedFile->getSize() : null,
        ]);
    }

    private function safeAudioExtension(TemporaryUploadedFile $file, ?string $mime): string
    {
        return in_array($mime, ['audio/mpeg', 'audio/mp3'], true) ? 'mp3' : ($file->extension() ?: 'webm');
    }

    private function notifyManagement(string $message, ?string $url = null): void
    {
        $availableRoles = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', ['Admin', 'HR', 'ManagementEmployee'])
            ->pluck('name')
            ->all();

        if (empty($availableRoles)) {
            return;
        }

        User::query()
            ->where('id', '!=', Auth::id())
            ->whereHas('roles', function ($query) use ($availableRoles) {
                $query->whereIn('name', $availableRoles)->where('guard_name', 'web');
            })
            ->get()
            ->each(function (User $user) use ($message, $url) {
                $user->notify(new DefaultNotification(
                    Auth::id(),
                    $message,
                    $url ?: route('management-complaints', absolute: false),
                    'ti ti-alert-triangle'
                ));
            });
    }
}
