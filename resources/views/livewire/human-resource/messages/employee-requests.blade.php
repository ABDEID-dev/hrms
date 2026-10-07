<div>
  @php
    $configData = Helper::appClasses();
  @endphp

  @section('title', __('ui.employee_requests_management'))

  @section('page-style')
    <style>
      .employee-requests-page .request-card {
          border: 1px solid rgba(var(--bs-border-color-rgb), 0.38);
          border-radius: 1rem;
      }

      .employee-requests-page .request-card.is-selected {
          border-color: rgba(115, 103, 240, 0.7);
          box-shadow: 0 0 0 0.2rem rgba(115, 103, 240, 0.12);
      }

      .employee-requests-page .request-meta {
          font-size: 0.9rem;
      }

      .employee-requests-page .request-detail-box {
          border: 1px solid rgba(var(--bs-border-color-rgb), 0.35);
          border-radius: 0.85rem;
          padding: 1rem;
          background: rgba(var(--bs-body-bg-rgb), 0.5);
      }

      .employee-requests-page .request-body {
          white-space: pre-wrap;
          line-height: 1.8;
      }

        .employee-requests-page .review-panel {
          margin-top: 1rem;
          padding: 1rem;
          border: 1px solid rgba(var(--bs-primary-rgb), 0.25);
          border-radius: 0.65rem;
          background: rgba(var(--bs-primary-rgb), 0.035);
        }

        .employee-requests-page .review-panel-header {
          display: flex;
          align-items: center;
          gap: 0.65rem;
          margin-bottom: 1rem;
        }

        .employee-requests-page .review-panel-icon {
          display: grid;
          width: 38px;
          height: 38px;
          flex: 0 0 38px;
          place-items: center;
          border-radius: 0.4rem;
          color: var(--bs-primary);
          background: rgba(var(--bs-primary-rgb), 0.12);
          font-size: 1.15rem;
        }

        .employee-requests-page .review-panel-actions {
          display: flex;
          align-items: center;
          justify-content: space-between;
          gap: 0.75rem;
          flex-wrap: wrap;
          margin-top: 1rem;
          padding-top: 1rem;
          border-top: 1px solid rgba(var(--bs-border-color-rgb), 0.45);
        }

        .employee-requests-page .review-decision-actions {
          display: flex;
          align-items: center;
          gap: 0.5rem;
          flex-wrap: wrap;
        }

        @media (max-width: 575.98px) {
          .employee-requests-page .review-panel {
            padding: 0.8rem;
          }

          .employee-requests-page .review-panel-actions,
          .employee-requests-page .review-decision-actions {
            width: 100%;
          }

          .employee-requests-page .review-decision-actions .btn,
          .employee-requests-page .review-panel-actions > .btn {
            flex: 1 1 auto;
          }
        }
    </style>
  @endsection

  @include('_partials/_alerts/alert-general')

  <div class="employee-requests-page">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">{{ __('ui.employee_requests_management') }}</h5>
        <div class="d-flex gap-2 col-lg-5">
          <input wire:model.live="searchTerm" type="text" class="form-control" placeholder="{{ __('ui.search_requests') }}">
          <select wire:model.live="statusFilter" class="form-select">
            <option value="">{{ __('ui.all_statuses') }}</option>
            <option value="pending">{{ __('ui.pending') }}</option>
            <option value="replied">{{ __('ui.replied') }}</option>
            <option value="approved">{{ __('ui.approved') }}</option>
            <option value="rejected">{{ __('ui.rejected') }}</option>
            <option value="cancelled">{{ __('ui.cancelled') }}</option>
          </select>
        </div>
      </div>
    </div>

    <div class="row g-4">
      @forelse ($requests as $request)
        <div class="col-12">
          <div id="request-{{ $request->id }}" class="card request-card {{ $selectedRequestId === $request->id ? 'is-selected' : '' }}">
            <div class="card-body">
              <div class="d-flex justify-content-between gap-3 flex-wrap">
                <div>
                  <h5 class="mb-1">{{ $request->title }}</h5>
                  <div class="text-muted request-meta">
                    {{ __('ui.employee_id') }}: {{ $request->employee?->id ?? '---' }} |
                    {{ $request->employee?->full_name ?? '---' }} |
                    {{ __('ui.request_type') }}: {{ __('ui.request_type_'.$request->type) }}
                  </div>
                </div>
                <div class="text-end">
                  <span class="badge bg-label-primary">{{ __('ui.'.$request->status) }}</span>
                  <div class="small text-muted mt-2">{{ $request->created_at->translatedFormat('Y-m-d H:i') }}</div>
                </div>
              </div>

              @if (! is_null($request->amount))
                <div class="mt-3 request-detail-box">
                  <div class="small text-muted">{{ $request->type === 'advance' ? __('ui.advance_requested_amount') : __('ui.amount') }}</div>
                  <div class="h5 mb-0 text-warning">AED {{ number_format($request->amount, 2) }}</div>
                  @if($request->type === 'advance' && $request->status === 'approved')
                    <div class="small text-success mt-2">
                      <i class="ti ti-circle-check me-1"></i>{{ __('ui.advance_will_be_deducted_from_salary') }}
                    </div>
                  @endif
                </div>
              @endif

              @if ($request->type === 'complaint')
                <div class="row g-3 mt-1">
                  <div class="col-md-4">
                    <div class="small text-muted">{{ __('ui.complainant_name') }}</div>
                    <div class="fw-semibold">{{ $request->complainant_name ?: ($request->employee?->full_name ?? '---') }}</div>
                  </div>
                  <div class="col-md-4">
                    <div class="small text-muted">{{ __('ui.complaint_against') }}</div>
                    <div class="fw-semibold">{{ $request->complaintAgainstEmployee?->full_name ?: ($request->complaint_against_other ?: '---') }}</div>
                  </div>
                  <div class="col-md-4">
                    @php
                      preg_match('/(?:File, Image, Voice, or Video|ملف أو صورة أو صوت أو فيديو):\s*(.+)/u', (string) $request->body, $fallbackAttachmentMatch);
                      preg_match('/(?:Voice Recording|تسجيل صوتي):\s*(.+)/u', (string) $request->body, $fallbackVoiceMatch);
                      $fallbackAttachmentPath = trim($fallbackAttachmentMatch[1] ?? '');
                      $fallbackVoicePath = trim($fallbackVoiceMatch[1] ?? '');
                    @endphp
                    <div class="small text-muted">{{ __('ui.attachments') }}</div>
                    <div class="d-flex flex-wrap gap-2 mt-1">
                      @if ($request->attachment_path)
                        <a href="{{ route('employee-complaints-file', ['path' => ltrim($request->attachment_path, '/')]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                          <i class="ti ti-paperclip me-1"></i>{{ $request->attachment_original_name ?: __('ui.attachment_or_media') }}
                        </a>
                      @elseif ($fallbackAttachmentPath)
                        <a href="{{ route('employee-complaints-file', ['path' => ltrim($fallbackAttachmentPath, '/')]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                          <i class="ti ti-paperclip me-1"></i>{{ __('ui.attachment_or_media') }}
                        </a>
                      @endif
                      @if ($request->voice_path)
                        <a href="{{ route('employee-complaints-file', ['path' => ltrim($request->voice_path, '/')]) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                          <i class="ti ti-device-audio-tape me-1"></i>{{ __('ui.voice_recording') }}
                        </a>
                      @elseif ($fallbackVoicePath)
                        <a href="{{ route('employee-complaints-file', ['path' => ltrim($fallbackVoicePath, '/')]) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                          <i class="ti ti-device-audio-tape me-1"></i>{{ __('ui.voice_recording') }}
                        </a>
                      @endif
                      @if (! $request->attachment_path && ! $request->voice_path && ! $fallbackAttachmentPath && ! $fallbackVoicePath)
                        <span class="text-muted small">---</span>
                      @endif
                    </div>
                  </div>
                </div>
              @endif

              <div class="mt-3 request-detail-box">
                <div class="small text-muted mb-2">{{ $request->type === 'complaint' ? __('ui.complaint_text') : __('ui.request_details') }}</div>
                <div class="request-body">{{ $request->body ?: '---' }}</div>
              </div>

              @if ($request->admin_response)
                <div class="alert alert-info mt-3 mb-0">
                  <strong>{{ __('ui.admin_response') }}:</strong>
                  <div class="mt-1" style="white-space: pre-wrap;">{{ $request->admin_response }}</div>
                </div>
              @endif

              @if ($this->canReviewRequests() && $selectedRequestId !== $request->id)
                <div class="mt-3">
                  <button wire:click="selectRequest({{ $request->id }})" type="button" class="btn btn-sm btn-outline-primary">
                    <i class="ti ti-message-reply me-1"></i>{{ __('ui.reply_or_update') }}
                  </button>
                </div>
              @endif

              @if ($this->canReviewRequests() && $selectedRequestId === $request->id)
                <div class="review-panel">
                  <div class="review-panel-header">
                    <span class="review-panel-icon" aria-hidden="true"><i class="ti ti-message-edit"></i></span>
                    <div>
                      <h6 class="mb-1">{{ __('ui.reply_or_update') }}</h6>
                      <div class="small text-muted">{{ $request->title }}</div>
                    </div>
                  </div>
                  @if($request->type === 'advance' && $this->canApproveRequests())
                    <div class="row g-3 mb-3">
                      <div class="col-md-4">
                        <label class="form-label">{{ __('ui.approved_advance_amount') }}</label>
                        <input
                          wire:model.defer="approvedAmount"
                          type="number"
                          min="1"
                          max="{{ (float) $request->amount }}"
                          step="0.01"
                          class="form-control @error('approvedAmount') is-invalid @enderror"
                        >
                        @error('approvedAmount')
                          <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                      </div>
                    </div>
                  @endif
                  <label class="form-label">{{ __('ui.admin_response') }}</label>
                  <textarea wire:model.defer="adminResponse" class="form-control @error('adminResponse') is-invalid @enderror" rows="4"></textarea>
                  @error('adminResponse')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror

                  <div class="review-panel-actions">
                    <button wire:click="cancelReview" type="button" class="btn btn-label-secondary">
                      <i class="ti ti-x me-1"></i>{{ __('ui.cancel') }}
                    </button>
                    <div class="review-decision-actions">
                      @if($this->canSaveRequestResponse())
                        <button wire:click="saveResponse({{ $request->id }})" type="button" class="btn btn-primary">
                          <i class="ti ti-device-floppy me-1"></i>{{ __('ui.save_response') }}
                        </button>
                      @endif
                      @if($this->canApproveRequests())
                        <button wire:click="approveRequest({{ $request->id }})" type="button" class="btn btn-success">
                          <i class="ti ti-check me-1"></i>{{ __('ui.approve') }}
                        </button>
                      @endif
                      @if($this->canRejectRequests())
                        <button wire:click="rejectRequest({{ $request->id }})" type="button" class="btn btn-danger">
                          <i class="ti ti-x me-1"></i>{{ __('ui.reject') }}
                        </button>
                      @endif
                      @if($this->canCancelRequests())
                        <button wire:click="cancelRequest({{ $request->id }})" type="button" class="btn btn-label-secondary">
                          <i class="ti ti-ban me-1"></i>{{ __('ui.cancel_request') }}
                        </button>
                      @endif
                    </div>
                  </div>
                </div>
              @endif
            </div>
          </div>
        </div>
      @empty
        <div class="col-12">
          <div class="card">
            <div class="card-body text-center text-muted py-5">
              {{ __('ui.no_requests_yet') }}
            </div>
          </div>
        </div>
      @endforelse
    </div>

    <div class="mt-4">
      {{ $requests->links() }}
    </div>
  </div>
</div>
