<div>
  @section('title', __('ui.complaints_management'))

  @section('page-style')
    <style>
      .complaints-management .complaint-card {
          border: 1px solid rgba(var(--bs-border-color-rgb), 0.38);
          border-radius: 1rem;
      }

      .complaints-management .complaint-card.is-selected {
          border-color: rgba(234, 84, 85, 0.7);
          box-shadow: 0 0 0 0.2rem rgba(234, 84, 85, 0.12);
      }

      .complaints-management .complaint-body {
          white-space: pre-wrap;
      }

      .complaints-management .document-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
          gap: 0.9rem;
      }

      .complaints-management .document-card {
          border: 1px solid rgba(var(--bs-border-color-rgb), 0.35);
          border-radius: 0.85rem;
          padding: 0.8rem;
          background: rgba(var(--bs-body-bg-rgb), 0.45);
      }

      .complaints-management .document-card img,
      .complaints-management .document-card video {
          width: 100%;
          max-height: 240px;
          object-fit: contain;
          border-radius: 0.6rem;
          background: rgba(0, 0, 0, 0.12);
      }

      .complaints-management .document-card audio {
          width: 100%;
      }

      @media (max-width: 575.98px) {
          .complaints-management .card-header {
              align-items: stretch !important;
          }

          .complaints-management .filters {
              width: 100%;
              flex-direction: column;
          }

          .complaints-management .complaint-actions .btn {
              width: 100%;
          }
      }
    </style>
  @endsection

  @include('_partials/_alerts/alert-general')

  <div class="complaints-management">
    <div class="card mb-4">
      <div class="card-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div>
          <h5 class="mb-0">
            <i class="ti ti-alert-triangle text-danger me-1"></i>{{ __('ui.complaints_management') }}
          </h5>
          <small class="text-muted">{{ __('ui.complaints_management_hint') }}</small>
        </div>
        <div class="filters d-flex gap-2 col-lg-5">
          <input wire:model.live.debounce.300ms="searchTerm" type="text" class="form-control" placeholder="{{ __('ui.search_complaints') }}">
          <select wire:model.live="statusFilter" class="form-select">
            <option value="">{{ __('ui.all_statuses') }}</option>
            <option value="pending">{{ __('ui.pending') }}</option>
            <option value="replied">{{ __('ui.replied') }}</option>
            <option value="approved">{{ __('ui.approved') }}</option>
            <option value="rejected">{{ __('ui.rejected') }}</option>
          </select>
        </div>
      </div>
    </div>

    <div class="row g-4">
      @forelse ($complaints as $complaint)
        <div class="col-12">
          <div id="complaint-{{ $complaint->id }}" class="card complaint-card {{ $selectedComplaintId === $complaint->id ? 'is-selected' : '' }}">
            <div class="card-body">
              <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                <div>
                  <h5 class="mb-1">{{ $complaint->title }}</h5>
                  <div class="text-muted small">
                    {{ __('ui.complainant_name') }}: {{ $complaint->complainant_name ?: ($complaint->employee?->full_name ?? '---') }}
                    <span class="mx-1">|</span>
                    {{ __('ui.employee_id') }}: {{ $complaint->employee?->id ?? '---' }}
                  </div>
                  <div class="text-muted small mt-1">
                    {{ __('ui.complaint_against') }}:
                    {{ $complaint->complaintAgainstEmployee?->full_name ?: ($complaint->complaint_against_other ?: '---') }}
                  </div>
                </div>
                <div class="text-lg-end">
                  <span class="badge {{ $complaint->status === 'approved' ? 'bg-label-success' : ($complaint->status === 'rejected' ? 'bg-label-danger' : ($complaint->status === 'replied' ? 'bg-label-info' : 'bg-label-primary')) }}">
                    {{ __('ui.'.$complaint->status) }}
                  </span>
                  <div class="small text-muted mt-2">{{ $complaint->created_at->translatedFormat('Y-m-d H:i') }}</div>
                </div>
              </div>

              <div class="complaint-body mt-3">{{ $complaint->body ?: '---' }}</div>

              @php
                preg_match('/(?:File, Image, Voice, or Video|ملف أو صورة أو صوت أو فيديو):\s*(.+)/u', (string) $complaint->body, $fallbackAttachmentMatch);
                preg_match('/(?:Voice Recording|تسجيل صوتي):\s*(.+)/u', (string) $complaint->body, $fallbackVoiceMatch);
                $fallbackAttachmentPath = trim($fallbackAttachmentMatch[1] ?? '');
                $fallbackVoicePath = trim($fallbackVoiceMatch[1] ?? '');
              @endphp

              <div class="d-flex flex-wrap gap-2 mt-3">
                @if ($complaint->attachment_path)
                  <a href="{{ route('employee-complaints-file', ['path' => ltrim($complaint->attachment_path, '/')]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="ti ti-paperclip me-1"></i>{{ $complaint->attachment_original_name ?: __('ui.attachment_or_media') }}
                  </a>
                @elseif ($fallbackAttachmentPath)
                  <a href="{{ route('employee-complaints-file', ['path' => ltrim($fallbackAttachmentPath, '/')]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <i class="ti ti-paperclip me-1"></i>{{ __('ui.attachment_or_media') }}
                  </a>
                @endif
                @if ($complaint->voice_path)
                  <a href="{{ route('employee-complaints-file', ['path' => ltrim($complaint->voice_path, '/')]) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                    <i class="ti ti-device-audio-tape me-1"></i>{{ __('ui.voice_recording') }}
                  </a>
                @elseif ($fallbackVoicePath)
                  <a href="{{ route('employee-complaints-file', ['path' => ltrim($fallbackVoicePath, '/')]) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                    <i class="ti ti-device-audio-tape me-1"></i>{{ __('ui.voice_recording') }}
                  </a>
                @endif
              </div>

              @if ($complaint->relationLoaded('visibleDocuments') && $complaint->visibleDocuments->count())
                <div class="document-grid mt-3">
                  @foreach ($complaint->visibleDocuments as $document)
                    <div class="document-card">
                      <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                        <div class="fw-semibold text-truncate">{{ $document->original_name ?: __('ui.attachment_or_media') }}</div>
                        <span class="badge bg-label-secondary">{{ __('ui.document_kind_'.$document->kind) }}</span>
                      </div>

                      @if ($document->is_image)
                        <a href="{{ $document->url }}" target="_blank">
                          <img src="{{ $document->url }}" alt="{{ $document->original_name }}">
                        </a>
                      @elseif ($document->is_video)
                        <video src="{{ $document->url }}" controls preload="metadata"></video>
                      @elseif ($document->is_audio)
                        <audio src="{{ $document->url }}" controls preload="metadata"></audio>
                      @else
                        <a href="{{ $document->url }}" target="_blank" class="btn btn-outline-primary btn-sm">
                          <i class="ti ti-download me-1"></i>{{ __('ui.open_document') }}
                        </a>
                      @endif

                      <div class="d-flex flex-wrap gap-2 mt-3">
                        <a href="{{ $document->url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                          <i class="ti ti-external-link me-1"></i>{{ __('ui.open_document') }}
                        </a>
                        <button wire:click="removeDocumentFromComplaint({{ $document->id }})" type="button" class="btn btn-sm btn-outline-warning">
                          <i class="ti ti-trash me-1"></i>{{ __('ui.remove_from_complaint') }}
                        </button>
                      </div>
                    </div>
                  @endforeach
                </div>
              @endif

              @if ($complaint->admin_response)
                <div class="alert alert-info mt-3 mb-0">
                  <strong>{{ __('ui.admin_response') }}:</strong>
                  <div class="mt-1 complaint-body">{{ $complaint->admin_response }}</div>
                </div>
              @endif

              <div class="complaint-actions d-flex flex-wrap gap-2 mt-3">
                <button wire:click="selectComplaint({{ $complaint->id }})" type="button" class="btn btn-outline-primary btn-sm">
                  <i class="ti ti-message-reply me-1"></i>{{ __('ui.reply_or_update') }}
                </button>
                <button wire:click="approveComplaint({{ $complaint->id }})" type="button" class="btn btn-success btn-sm">
                  <i class="ti ti-check me-1"></i>{{ __('ui.approve') }}
                </button>
                <button wire:click="rejectComplaint({{ $complaint->id }})" type="button" class="btn btn-danger btn-sm">
                  <i class="ti ti-x me-1"></i>{{ __('ui.reject') }}
                </button>
              </div>

              @if ($selectedComplaintId === $complaint->id)
                <div class="mt-4">
                  <label class="form-label">{{ __('ui.admin_response') }}</label>
                  <textarea wire:model.defer="adminResponse" class="form-control @error('adminResponse') is-invalid @enderror" rows="4"></textarea>
                  @error('adminResponse')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                  <button wire:click="saveResponse({{ $complaint->id }})" type="button" class="btn btn-primary mt-3">
                    {{ __('ui.save_response') }}
                  </button>
                </div>
              @endif
            </div>
          </div>
        </div>
      @empty
        <div class="col-12">
          <div class="card">
            <div class="card-body text-center text-muted py-5">
              {{ __('ui.no_complaints_yet') }}
            </div>
          </div>
        </div>
      @endforelse
    </div>

    <div class="mt-4">
      {{ $complaints->links() }}
    </div>
  </div>
</div>
