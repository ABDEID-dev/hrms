<div>
  @section('title', 'Employee Documents')

  @section('page-style')
    <style>
      .employee-document-card {
        border: 1px solid rgba(75, 70, 92, .12);
        border-radius: .5rem;
        transition: border-color .2s ease, box-shadow .2s ease;
      }

      .employee-document-card:hover {
        border-color: rgba(115, 103, 240, .45);
        box-shadow: 0 .125rem .5rem rgba(75, 70, 92, .08);
      }

      .document-preview {
        height: 132px;
        border-radius: .375rem;
        background: rgba(75, 70, 92, .04);
        overflow: hidden;
      }

      .document-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }

      .document-viewer-frame {
        width: 100%;
        height: min(76vh, 820px);
        border: 0;
        border-radius: .5rem;
        background: rgba(75, 70, 92, .04);
      }

      .document-viewer-image {
        max-width: 100%;
        max-height: min(76vh, 820px);
        border-radius: .5rem;
        object-fit: contain;
        background: rgba(75, 70, 92, .04);
      }
    </style>
  @endsection

  @unless($documentsTableExists)
    <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
      <i class="ti ti-database-exclamation"></i>
      <div>{{ __('جدول مستندات الموظفين غير موجود بعد. برجاء تشغيل migrations أولاً: php artisan migrate') }}</div>
    </div>
  @endunless

  <div class="row g-4">
    <div class="col-xl-4 col-lg-5">
      <div class="card">
        <div class="card-header">
          <h5 class="card-title mb-3">{{ __('قائمة الموظفين') }}</h5>
          <input wire:model.live="searchTerm" type="text" class="form-control" placeholder="{{ __('Search (ID, Name...)') }}">
        </div>
        <div class="list-group list-group-flush">
          @forelse($employees as $employee)
            <button type="button" wire:click="selectEmployee({{ $employee->id }})" class="list-group-item list-group-item-action {{ (int) $selectedEmployeeId === (int) $employee->id ? 'active' : '' }}">
              <div class="d-flex align-items-center gap-2">
                <img src="{{ route('employee-profile-photo', ['employee' => $employee->id, 'v' => optional($employee->updated_at)->timestamp]) }}" class="rounded-circle" width="34" height="34" alt="Avatar">
                <div class="flex-grow-1 text-start">
                  <div class="fw-semibold">{{ $employee->full_name ?: $employee->id }}</div>
                  <small class="{{ (int) $selectedEmployeeId === (int) $employee->id ? 'text-white-50' : 'text-muted' }}">#{{ $employee->id }} - {{ $documentsTableExists ? $employee->documents_count : 0 }} {{ __('مستند') }}</small>
                </div>
              </div>
            </button>
          @empty
            <div class="p-4 text-center text-muted">{{ __('No data found') }}</div>
          @endforelse
        </div>
        <div class="card-footer">
          {{ $employees->links() }}
        </div>
      </div>
    </div>

    <div class="col-xl-8 col-lg-7">
      @if($selectedEmployee)
        <div class="card mb-4">
          <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
              <img src="{{ route('employee-profile-photo', ['employee' => $selectedEmployee->id, 'v' => optional($selectedEmployee->updated_at)->timestamp]) }}" class="rounded-circle" width="48" height="48" alt="Avatar">
              <div>
                <h5 class="mb-1">{{ $selectedEmployee->full_name }}</h5>
                <small class="text-muted">#{{ $selectedEmployee->id }} - {{ $selectedEmployee->national_number ?: __('No identity number') }}</small>
              </div>
            </div>
            <a href="{{ route('structure-employees-info', $selectedEmployee->id) }}" class="btn btn-label-primary">
              <i class="ti ti-user me-1"></i>{{ __('بيانات الموظف') }}
            </a>
          </div>

          @can('manage employee documents')
            @if($documentsTableExists)
            <div class="card-body border-top">
              <form wire:submit.prevent="uploadDocument" class="row g-3 align-items-end">
                <div class="col-md-4">
                  <label class="form-label">{{ __('نوع المستند') }}</label>
                  <select wire:model="documentType" class="form-select">
                    @foreach($documentTypes as $value => $label)
                      <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                  </select>
                  @error('documentType') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-4">
                  <label class="form-label">{{ __('عنوان اختياري') }}</label>
                  <input wire:model="documentTitle" type="text" class="form-control" placeholder="{{ __('مثال: هوية أمامية') }}">
                  @error('documentTitle') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
                <div class="col-md-4">
                  <label class="form-label">{{ __('PDF أو صورة') }}</label>
                  <input wire:model="documentFile" type="file" class="form-control" accept=".pdf,image/jpeg,image/png">
                  @error('documentFile') <small class="text-danger d-block">{{ $message }}</small> @enderror
                </div>
                <div class="col-12">
                  <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="documentFile,uploadDocument">
                    <i class="ti ti-upload me-1"></i>{{ __('رفع المستند') }}
                  </button>
                  <span class="text-muted ms-2" wire:loading wire:target="documentFile,uploadDocument">{{ __('جاري التحميل...') }}</span>
                </div>
              </form>
            </div>
            @endif
          @endcan
        </div>

        @if($documentsTableExists)
          <div class="row g-3">
          @forelse($selectedEmployee->documents as $document)
            <div class="col-md-6">
              <div class="employee-document-card p-3 h-100">
                <div class="document-preview mb-3 d-flex align-items-center justify-content-center">
                  @if($document->is_image)
                    <img src="{{ $document->url }}" alt="{{ $document->title }}">
                  @elseif($document->is_pdf)
                    <i class="ti ti-file-type-pdf text-danger" style="font-size: 4rem;"></i>
                  @else
                    <i class="ti ti-file text-muted" style="font-size: 4rem;"></i>
                  @endif
                </div>
                <div class="d-flex align-items-start justify-content-between gap-2">
                  <div>
                    <span class="badge bg-label-primary mb-2">{{ $document->type_label }}</span>
                    <h6 class="mb-1">{{ $document->title ?: $document->original_name }}</h6>
                    <small class="text-muted d-block">{{ $document->original_name }}</small>
                    <small class="text-muted d-block">{{ $document->created_at?->format('Y-m-d h:i A') }} - {{ $document->readable_size }}</small>
                    @if($document->uploader)
                      <small class="text-muted d-block">{{ __('بواسطة') }} {{ $document->uploader->name }}</small>
                    @endif
                  </div>
                  <div class="d-flex gap-1">
                    <button type="button" wire:click="previewDocument({{ $document->id }})" class="btn btn-sm btn-icon btn-outline-primary" title="{{ __('عرض') }}">
                      <i class="ti ti-eye"></i>
                    </button>
                    @can('manage employee documents')
                      <button type="button" wire:click="confirmDeleteDocument({{ $document->id }})" class="btn btn-sm btn-icon btn-outline-danger" title="{{ __('Delete') }}">
                        <i class="ti ti-trash"></i>
                      </button>
                    @endcan
                  </div>
                </div>
                @if(Auth::user()?->can('manage employee documents') && $confirmedDocumentId === $document->id)
                  <button wire:click="deleteDocument({{ $document->id }})" type="button" class="btn btn-sm btn-danger mt-3">{{ __('Sure?') }}</button>
                @endif
              </div>
            </div>
          @empty
            <div class="col-12">
              <div class="card">
                <div class="card-body text-center py-5">
                  <i class="ti ti-folder-open text-muted mb-2" style="font-size: 3rem;"></i>
                  <h5 class="mb-1">{{ __('لا توجد مستندات لهذا الموظف') }}</h5>
                  <p class="text-muted mb-0">{{ __('ارفع الهوية أو إيصال السلفة أو إيصال تحصيل الراتب ليظهر هنا بشكل مرتب.') }}</p>
                </div>
              </div>
            </div>
          @endforelse
          </div>
        @else
          <div class="card">
            <div class="card-body text-center py-5">
              <i class="ti ti-database-exclamation text-warning mb-2" style="font-size: 3rem;"></i>
              <h5 class="mb-1">{{ __('مطلوب تحديث قاعدة البيانات') }}</h5>
              <p class="text-muted mb-0">{{ __('بعد تشغيل php artisan migrate ستظهر خانات الرفع ومستندات الموظف هنا.') }}</p>
            </div>
          </div>
        @endif
      @else
        <div class="card">
          <div class="card-body text-center py-5">
            <h5 class="mb-1">{{ __('اختر موظفاً') }}</h5>
            <p class="text-muted mb-0">{{ __('اختر موظفاً من القائمة لعرض مستنداته.') }}</p>
          </div>
        </div>
      @endif
    </div>
  </div>

  <div wire:ignore.self class="modal fade" id="documentPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h5 class="modal-title">{{ $previewDocument?->title ?: __('عرض المستند') }}</h5>
            @if($previewDocument)
              <small class="text-muted">{{ $previewDocument->type_label }} - {{ $previewDocument->original_name }}</small>
            @endif
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="closeDocumentPreview"></button>
        </div>
        <div class="modal-body text-center">
          @if($previewDocument)
            @if($previewDocument->is_image)
              <img src="{{ $previewDocument->url }}" class="document-viewer-image" alt="{{ $previewDocument->title }}">
            @elseif($previewDocument->is_pdf)
              <iframe src="{{ $previewDocument->url }}" class="document-viewer-frame" title="{{ $previewDocument->title }}"></iframe>
            @else
              <div class="py-5">
                <i class="ti ti-file text-muted mb-2" style="font-size: 4rem;"></i>
                <h5>{{ __('لا يمكن معاينة هذا النوع داخل النظام') }}</h5>
              </div>
            @endif
          @endif
        </div>
        @if($previewDocument)
          <div class="modal-footer">
            <a href="{{ $previewDocument->url }}" target="_blank" class="btn btn-label-secondary">
              <i class="ti ti-external-link me-1"></i>{{ __('فتح في تبويب جديد') }}
            </a>
          </div>
        @endif
      </div>
    </div>
  </div>

  @push('custom-scripts')
    <script>
      document.addEventListener('livewire:init', function () {
        Livewire.on('showDocumentPreview', function () {
          const modalElement = document.getElementById('documentPreviewModal');
          if (modalElement) {
            bootstrap.Modal.getOrCreateInstance(modalElement).show();
          }
        });
      });
    </script>
  @endpush
</div>
