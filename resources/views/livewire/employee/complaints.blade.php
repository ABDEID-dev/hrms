<div>
  @section('title', __('ui.complaints'))

  @section('page-style')
    <style>
      .employee-complaints .complaint-hero {
          border: 1px solid rgba(234, 84, 85, 0.24);
          background: linear-gradient(135deg, rgba(234, 84, 85, 0.16), rgba(115, 103, 240, 0.12));
      }

      .employee-complaints .complaint-form textarea {
          min-height: 150px;
          resize: vertical;
      }

      .employee-complaints .voice-panel {
          border: 1px dashed rgba(115, 103, 240, 0.45);
          border-radius: 1rem;
          padding: 1rem;
          background: rgba(var(--bs-body-bg-rgb), 0.48);
      }

      .employee-complaints .recording-meter {
          height: 8px;
          border-radius: 999px;
          background: rgba(234, 84, 85, 0.14);
          overflow: hidden;
          margin-top: 0.85rem;
      }

      .employee-complaints .recording-meter span {
          display: block;
          height: 100%;
          width: 34%;
          border-radius: inherit;
          background: #ea5455;
          animation: recording-wave 1s ease-in-out infinite alternate;
      }

      .employee-complaints .recording-dot {
          width: 10px;
          height: 10px;
          border-radius: 999px;
          background: #ea5455;
          display: inline-block;
          animation: recording-pulse 1s infinite;
      }

      .employee-complaints .voice-preview-list {
          display: flex;
          flex-direction: column;
          gap: 0.75rem;
          margin-top: 1rem;
      }

      .employee-complaints .voice-preview-item {
          border: 1px solid rgba(var(--bs-border-color-rgb), 0.35);
          border-radius: 0.8rem;
          padding: 0.75rem;
          background: rgba(var(--bs-body-bg-rgb), 0.42);
      }

      .employee-complaints .complaint-document {
          border: 1px solid rgba(var(--bs-border-color-rgb), 0.32);
          border-radius: 0.75rem;
          padding: 0.75rem;
          background: rgba(var(--bs-body-bg-rgb), 0.35);
      }

      .employee-complaints .complaint-document audio,
      .employee-complaints .complaint-document video {
          max-height: 220px;
      }

      @keyframes recording-wave {
          from { transform: translateX(-35%); }
          to { transform: translateX(210%); }
      }

      @keyframes recording-pulse {
          0%, 100% { opacity: 0.35; transform: scale(0.9); }
          50% { opacity: 1; transform: scale(1.15); }
      }

      .employee-complaints .complaint-item {
          border: 1px solid rgba(var(--bs-border-color-rgb), 0.35);
          border-radius: 1rem;
          background: rgba(var(--bs-body-bg-rgb), 0.48);
          overflow: hidden;
      }

      .employee-complaints .complaint-summary {
          width: 100%;
          border: 0;
          background: transparent;
          color: inherit;
          padding: 1rem;
          text-align: inherit;
      }

      .employee-complaints .complaint-summary:hover {
          background: rgba(115, 103, 240, 0.08);
      }

      .employee-complaints .complaint-details {
          border-top: 1px solid rgba(var(--bs-border-color-rgb), 0.22);
          padding: 1rem;
      }

      .employee-complaints .complaint-body-box,
      .employee-complaints .complaint-response-box {
          border-radius: 0.75rem;
          padding: 0.85rem;
          background: rgba(var(--bs-body-bg-rgb), 0.35);
          white-space: pre-wrap;
      }

      .employee-complaints .complaint-response-box {
          border: 1px solid rgba(40, 199, 111, 0.28);
          background: rgba(40, 199, 111, 0.08);
      }

      .employee-complaints .complaint-list {
          display: flex;
          flex-direction: column;
          gap: 0.9rem;
      }

      @media (max-width: 575.98px) {
          .employee-complaints .card-body {
              padding: 1rem;
          }

          .employee-complaints .complaint-hero h3 {
              font-size: 1.25rem;
          }

          .employee-complaints .voice-panel .btn,
          .employee-complaints .complaint-form .btn-danger {
              width: 100%;
              justify-content: center;
          }
      }
    </style>
  @endsection

  @include('_partials/_alerts/alert-general')

  <div class="employee-complaints">
    <div class="card complaint-hero mb-4">
      <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
          <span class="badge bg-label-danger mb-2">{{ __('ui.employee_portal') }}</span>
          <h3 class="mb-1">{{ __('ui.complaints') }}</h3>
          <p class="text-muted mb-0">{{ __('ui.complaints_hint') }}</p>
        </div>
        <span class="badge bg-label-primary">
          <i class="ti ti-shield-check me-1"></i>{{ __('ui.private_complaint_channel') }}
        </span>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-xl-7">
        <div class="card complaint-form">
          <div class="card-header">
            <h5 class="mb-0">{{ __('ui.new_complaint') }}</h5>
            <small class="text-muted">{{ __('ui.new_complaint_hint') }}</small>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">{{ __('ui.complainant_name') }}</label>
                <input wire:model.defer="complainantName" type="text" class="form-control @error('complainantName') is-invalid @enderror">
                @error('complainantName')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">{{ __('ui.complaint_title') }}</label>
                <input wire:model.defer="complaintTitle" type="text" class="form-control @error('complaintTitle') is-invalid @enderror">
                @error('complaintTitle')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-md-6">
                <label class="form-label">{{ __('ui.complaint_against') }}</label>
                <select wire:model.live="complaintAgainst" class="form-select @error('complaintAgainst') is-invalid @enderror">
                  <option value="">{{ __('ui.choose_employee_or_other') }}</option>
                  @foreach ($employees as $targetEmployee)
                    <option value="{{ $targetEmployee->id }}">
                      {{ $targetEmployee->full_name ?: $targetEmployee->id }}
                    </option>
                  @endforeach
                  <option value="other">{{ __('ui.other_person') }}</option>
                </select>
                @error('complaintAgainst')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              @if ($complaintAgainst === 'other')
                <div class="col-md-6">
                  <label class="form-label">{{ __('ui.other_person_name') }}</label>
                  <input wire:model.defer="complaintAgainstOther" type="text" class="form-control @error('complaintAgainstOther') is-invalid @enderror">
                  @error('complaintAgainstOther')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              @endif

              <div class="col-12">
                <label class="form-label">{{ __('ui.complaint_text') }}</label>
                <textarea wire:model.defer="complaintBody" class="form-control @error('complaintBody') is-invalid @enderror"></textarea>
                @error('complaintBody')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-12">
                <label class="form-label">{{ __('ui.attachment_or_media') }}</label>
                <input wire:model="attachment" type="file" class="form-control @error('attachment') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt,.mp3,.wav,.m4a,.ogg,.webm,.mp4,.mov,.avi,.mkv,audio/*,video/*">
                <div class="form-text">{{ __('ui.complaint_attachment_hint') }}</div>
                @error('attachment')
                  <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-12">
                <div class="voice-panel" x-data="complaintVoiceRecorder()" wire:ignore>
                  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                      <label class="form-label mb-1">{{ __('ui.voice_recording') }}</label>
                      <div class="text-muted small d-flex align-items-center gap-2">
                        <span x-show="isRecording" class="recording-dot"></span>
                        <span x-text="statusText"></span>
                        <span x-show="isRecording" class="text-danger fw-semibold" x-text="elapsedText"></span>
                      </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                      <button x-on:click="start" x-bind:disabled="isRecording" type="button" class="btn btn-outline-danger btn-sm">
                        <i class="ti ti-player-record me-1"></i>{{ __('ui.start_recording') }}
                      </button>
                      <button x-on:click="stop" x-bind:disabled="!isRecording" type="button" class="btn btn-outline-primary btn-sm">
                        <i class="ti ti-player-stop me-1"></i>{{ __('ui.stop_recording') }}
                      </button>
                      <button x-on:click="clear" type="button" class="btn btn-outline-secondary btn-sm">
                        <i class="ti ti-trash me-1"></i>{{ __('ui.clear_recording') }}
                      </button>
                    </div>
                  </div>

                  <div x-show="isRecording" class="recording-meter"><span></span></div>
                  <div class="voice-preview-list" x-show="recordings.length">
                    <template x-for="(recording, index) in recordings" :key="recording.id">
                      <div class="voice-preview-item">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                          <small class="text-muted" x-text="recording.name"></small>
                          <button x-on:click="remove(index)" type="button" class="btn btn-xs btn-outline-danger">
                            <i class="ti ti-trash me-1"></i>{{ __('ui.delete') }}
                          </button>
                        </div>
                        <audio x-bind:src="recording.url" controls class="w-100"></audio>
                      </div>
                    </template>
                  </div>
                  <input x-ref="voiceInput" wire:model="voiceRecordings" type="file" multiple class="d-none" accept="audio/mpeg,audio/mp3,audio/webm,audio/ogg,video/webm">

                </div>
                <div wire:loading wire:target="voiceRecordings" class="small text-primary mt-2">
                  {{ __('ui.uploading_voice_recording') }}
                </div>
                @error('voiceRecordings')
                  <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
                @error('voiceRecordings.*')
                  <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
              </div>

              <div class="col-12">
                <button wire:click="submitComplaint" wire:loading.attr="disabled" wire:target="attachment,voiceRecordings,submitComplaint" type="button" class="btn btn-danger">
                  <i class="ti ti-send me-1"></i>{{ __('ui.send_complaint') }}
                </button>
                <div wire:loading wire:target="attachment,voiceRecordings" class="small text-primary mt-2">
                  {{ __('ui.uploading_attachment') }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-5">
        <div class="card h-100">
          <div class="card-header">
            <h5 class="mb-0">{{ __('ui.my_recent_complaints') }}</h5>
            <small class="text-muted">{{ __('ui.my_recent_complaints_hint') }}</small>
          </div>
          <div class="card-body">
            @if ($recentComplaints->count())
              <div class="complaint-list">
                @foreach ($recentComplaints as $complaint)
                  <div class="complaint-item">
                    <button class="complaint-summary" type="button" data-bs-toggle="collapse" data-bs-target="#employee-complaint-{{ $complaint->id }}" aria-expanded="false" aria-controls="employee-complaint-{{ $complaint->id }}">
                      <div class="d-flex align-items-start justify-content-between gap-2">
                        <div class="min-w-0">
                          <div class="fw-semibold text-truncate">{{ $complaint->title }}</div>
                          <div class="small text-muted mt-1">{{ $complaint->created_at->translatedFormat('Y-m-d H:i') }}</div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                          <span class="badge bg-label-primary">{{ __('ui.'.$complaint->status) }}</span>
                          <i class="ti ti-chevron-down text-muted"></i>
                        </div>
                      </div>
                    </button>

                    <div class="collapse" id="employee-complaint-{{ $complaint->id }}">
                      <div class="complaint-details">
                        <div class="small text-muted mb-2">
                          {{ __('ui.complaint_against') }}:
                          {{ $complaint->complaintAgainstEmployee?->full_name ?: ($complaint->complaint_against_other ?: '---') }}
                        </div>

                        @php
                          $displayBody = collect(preg_split('/\r\n|\r|\n/', (string) $complaint->body))
                              ->reject(fn ($line) => str_contains($line, 'employee-complaints/') || str_contains($line, 'complaints/attachments/'))
                              ->implode(PHP_EOL);
                        @endphp
                        <div class="complaint-body-box small">{{ trim($displayBody) ?: $complaint->body }}</div>

                        @if ($complaint->admin_response)
                          <div class="mt-3">
                            <div class="fw-semibold mb-2">{{ __('ui.admin_response') }}</div>
                            <div class="complaint-response-box small">{{ $complaint->admin_response }}</div>
                          </div>
                        @endif

                        @if ($complaint->relationLoaded('visibleDocuments') && $complaint->visibleDocuments->count())
                          <div class="mt-3 d-flex flex-column gap-2">
                            @foreach ($complaint->visibleDocuments as $document)
                              <div class="complaint-document" wire:key="complaint-document-{{ $document->id }}">
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                  <small class="text-muted text-truncate">{{ $document->original_name ?: __('ui.open_document') }}</small>
                                  <button wire:click="removeDocumentFromComplaint({{ $document->id }})" wire:loading.attr="disabled" type="button" class="btn btn-xs btn-outline-warning flex-shrink-0">
                                    <i class="ti ti-trash me-1"></i>{{ __('ui.remove_from_complaint') }}
                                  </button>
                                </div>
                                @if ($document->is_audio)
                                  <audio src="{{ $document->url }}" controls class="w-100"></audio>
                                @elseif ($document->is_video)
                                  <video src="{{ $document->url }}" controls class="w-100 rounded"></video>
                                @elseif ($document->is_image)
                                  <img src="{{ $document->url }}" class="img-fluid rounded" alt="{{ $document->original_name }}">
                                @else
                                  <a href="{{ $document->url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    {{ $document->original_name ?: __('ui.open_document') }}
                                  </a>
                                @endif
                              </div>
                            @endforeach
                          </div>
                        @endif

                        <div class="mt-3">
                          <label class="form-label small">{{ __('ui.add_document_to_complaint') }}</label>
                          <input wire:model="extraDocuments.{{ $complaint->id }}" type="file" multiple class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.txt,.mp3,.wav,.m4a,.ogg,.webm,.mp4,.mov,.avi,.mkv,audio/*,video/*">
                          <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                            <button wire:click="addDocumentsToComplaint({{ $complaint->id }})" wire:loading.attr="disabled" wire:target="extraDocuments.{{ $complaint->id }},addDocumentsToComplaint" type="button" class="btn btn-sm btn-outline-primary">
                              <i class="ti ti-upload me-1"></i>{{ __('ui.upload_new_document') }}
                            </button>
                            <span wire:loading wire:target="extraDocuments.{{ $complaint->id }}" class="small text-primary">
                              {{ __('ui.uploading_attachment') }}
                            </span>
                          </div>
                          @error('extraDocuments.'.$complaint->id)
                            <div class="text-danger small mt-2">{{ $message }}</div>
                          @enderror
                          @error('extraDocuments.'.$complaint->id.'.*')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                          @enderror
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            @else
              <div class="text-center text-muted py-5">
                <i class="ti ti-inbox d-block fs-1 mb-2"></i>
                {{ __('ui.no_complaints_yet') }}
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  @push('custom-scripts')
    <script>
      function complaintVoiceRecorder() {
        return {
          isRecording: false,
          recorder: null,
          chunks: [],
          recordings: [],
          files: [],
          statusText: @js(__('ui.voice_ready_hint')),
          elapsedSeconds: 0,
          elapsedText: '00:00',
          timer: null,
          selectedMime: '',
          start() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
              this.statusText = @js(__('ui.voice_not_supported'));
              return;
            }

            const supportedTypes = ['audio/mpeg', 'audio/mp3', 'audio/webm;codecs=opus', 'audio/webm', 'audio/ogg;codecs=opus'];
            this.selectedMime = supportedTypes.find((type) => window.MediaRecorder && MediaRecorder.isTypeSupported(type)) || '';

            navigator.mediaDevices.getUserMedia({ audio: true }).then((stream) => {
              this.chunks = [];
              this.recorder = new MediaRecorder(stream, this.selectedMime ? { mimeType: this.selectedMime } : undefined);
              this.recorder.ondataavailable = (event) => {
                if (event.data && event.data.size > 0) {
                  this.chunks.push(event.data);
                }
              };
              this.recorder.onstop = () => {
                const mimeType = this.recorder.mimeType || this.selectedMime || 'audio/webm';
                const extension = mimeType.includes('mpeg') || mimeType.includes('mp3') ? 'mp3' : (mimeType.includes('ogg') ? 'ogg' : 'webm');
                const blob = new Blob(this.chunks, { type: mimeType });
                const recordingNumber = this.recordings.length + 1;
                const file = new File([blob], `complaint-voice-${recordingNumber}.${extension}`, { type: mimeType });
                const url = URL.createObjectURL(blob);
                this.files.push(file);
                this.recordings.push({
                  id: Date.now() + Math.random(),
                  name: `${@js(__('ui.voice_recording'))} ${recordingNumber}`,
                  url
                });
                this.syncInput();
                stream.getTracks().forEach((track) => track.stop());
                this.statusText = extension === 'mp3'
                  ? @js(__('ui.voice_recorded_mp3'))
                  : @js(__('ui.voice_recorded_browser_format'));
              };
              this.recorder.start();
              this.isRecording = true;
              this.elapsedSeconds = 0;
              this.elapsedText = '00:00';
              this.timer = setInterval(() => {
                this.elapsedSeconds += 1;
                const minutes = String(Math.floor(this.elapsedSeconds / 60)).padStart(2, '0');
                const seconds = String(this.elapsedSeconds % 60).padStart(2, '0');
                this.elapsedText = `${minutes}:${seconds}`;
              }, 1000);
              this.statusText = @js(__('ui.recording_now'));
            }).catch(() => {
              this.statusText = @js(__('ui.microphone_permission_denied'));
            });
          },
          stop() {
            if (this.recorder && this.isRecording) {
              this.recorder.stop();
              this.isRecording = false;
              clearInterval(this.timer);
            }
          },
          clear() {
            clearInterval(this.timer);
            this.isRecording = false;
            this.elapsedSeconds = 0;
            this.elapsedText = '00:00';
            this.chunks = [];
            this.recordings.forEach((recording) => URL.revokeObjectURL(recording.url));
            this.recordings = [];
            this.files = [];
            this.syncInput();
            this.statusText = @js(__('ui.voice_ready_hint'));
          },
          remove(index) {
            if (this.recordings[index]) {
              URL.revokeObjectURL(this.recordings[index].url);
            }
            this.recordings.splice(index, 1);
            this.files.splice(index, 1);
            this.syncInput();
            this.statusText = this.recordings.length
              ? @js(__('ui.voice_ready_hint'))
              : @js(__('ui.voice_ready_hint'));
          },
          syncInput() {
            const dataTransfer = new DataTransfer();
            this.files.forEach((file) => dataTransfer.items.add(file));
            this.$refs.voiceInput.files = dataTransfer.files;
            this.$refs.voiceInput.dispatchEvent(new Event('change', { bubbles: true }));
          }
        };
      }

      window.addEventListener('complaintSaved', () => {
        window.location.reload();
      });
    </script>
  @endpush
</div>
