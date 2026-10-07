<div>
  @section('title', __('ui.deleted_documents'))

  @include('_partials/_alerts/alert-general')

  <div class="card mb-4">
    <div class="card-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
      <div>
        <h5 class="mb-0">
          <i class="ti ti-archive text-warning me-1"></i>{{ __('ui.deleted_documents') }}
        </h5>
        <small class="text-muted">{{ __('ui.deleted_documents_hint') }}</small>
      </div>
      <input wire:model.live.debounce.300ms="searchTerm" type="text" class="form-control w-auto" placeholder="{{ __('ui.search_documents') }}">
    </div>
  </div>

  <div class="row g-4">
    @forelse ($documents as $document)
      <div class="col-xl-4 col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between gap-2 mb-2">
              <div class="fw-semibold text-truncate">{{ $document->original_name ?: __('ui.attachment_or_media') }}</div>
              <span class="badge bg-label-secondary">{{ __('ui.document_kind_'.$document->kind) }}</span>
            </div>

            <div class="small text-muted mb-3">
              {{ __('ui.employee_id') }}: {{ $document->employee?->id ?? '---' }}
              <br>
              {{ __('ui.complaint_title') }}: {{ $document->request?->title ?? '---' }}
              <br>
              {{ __('ui.removed_at') }}: {{ $document->removed_from_complaint_at?->translatedFormat('Y-m-d H:i') }}
            </div>

            @if ($document->is_image)
              <img src="{{ $document->url }}" alt="{{ $document->original_name }}" class="img-fluid rounded mb-3">
            @elseif ($document->is_video)
              <video src="{{ $document->url }}" controls preload="metadata" class="w-100 rounded mb-3"></video>
            @elseif ($document->is_audio)
              <audio src="{{ $document->url }}" controls preload="metadata" class="w-100 mb-3"></audio>
            @endif

            <div class="d-flex flex-wrap gap-2">
              <a href="{{ $document->url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="ti ti-external-link me-1"></i>{{ __('ui.open_document') }}
              </a>
              <button wire:click="restoreDocument({{ $document->id }})" type="button" class="btn btn-sm btn-success">
                <i class="ti ti-restore me-1"></i>{{ __('ui.restore') }}
              </button>
              <button wire:click="deleteDocumentFromSystem({{ $document->id }})" type="button" class="btn btn-sm btn-danger">
                <i class="ti ti-trash me-1"></i>{{ __('ui.delete_from_system') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12">
        <div class="card">
          <div class="card-body text-center text-muted py-5">
            {{ __('ui.no_deleted_documents') }}
          </div>
        </div>
      </div>
    @endforelse
  </div>

  <div class="mt-4">
    {{ $documents->links() }}
  </div>
</div>
