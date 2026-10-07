<div>
  @section('title', 'Services')

  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
      <h4 class="mb-1">{{ __('Services') }}</h4>
      <p class="text-muted mb-0">أدر خدمات الصالون وأسعارها، ويمكنك استخدام خيار الفرعين معًا للخدمات المشتركة.</p>
    </div>
    <button wire:click="showNewServiceModal" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#salonServiceModal">
      <span class="ti ti-plus me-1"></span>{{ __('Add New Service') }}
    </button>
  </div>

  <div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div class="d-flex gap-2 flex-wrap">
        <input wire:model.live="searchTerm" type="text" class="form-control" style="min-width: 220px;" placeholder="{{ __('Search services...') }}">
        <select wire:model.live="branchFilter" class="form-select" style="min-width: 220px;">
          <option value="">{{ __('All branches') }}</option>
          @foreach ($branches as $key => $branchName)
            <option value="{{ $key }}">{{ $branchName }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>{{ __('Name') }}</th>
            <th>{{ __('Branch') }}</th>
            <th>{{ __('Price') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($services as $service)
            <tr>
              <td>
                <div>{{ $service->name }}</div>
                @if ($service->english_name)
                  <div class="small text-muted">{{ $service->english_name }}</div>
                @endif
              </td>
              <td>{{ $branches[$service->branch] ?? $service->branch }}</td>
              <td>
                <div>{{ number_format((float) $service->price, 2) }}</div>
                @if ($service->price_note)
                  <div class="small text-muted">{{ $service->price_note }}</div>
                @endif
              </td>
              <td>
                <span class="badge {{ $service->is_active ? 'bg-label-success' : 'bg-label-secondary' }}">
                  {{ $service->is_active ? __('Active') : __('Inactive') }}
                </span>
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <button wire:click="showEditServiceModal({{ $service->id }})" type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#salonServiceModal">
                    <i class="ti ti-pencil"></i>
                  </button>
                  @if ($confirmedId === $service->id)
                    <button wire:click="deleteService({{ $service->id }})" type="button" class="btn btn-sm btn-danger">{{ __('Sure?') }}</button>
                  @else
                    <button wire:click="confirmDeleteService({{ $service->id }})" type="button" class="btn btn-sm btn-outline-danger">
                      <i class="ti ti-trash"></i>
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center py-5">
                <h5 class="mb-2">{{ __('No services added yet.') }}</h5>
                <p class="text-muted mb-0">{{ __('Add the first salon service and price to start invoicing.') }}</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="card-footer">
      {{ $services->links() }}
    </div>
  </div>

  <div wire:ignore.self class="modal fade" id="salonServiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ $isEdit ? __('Update Service') : __('Add New Service') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">{{ __('Service Name') }}</label>
            <input wire:model.defer="serviceName" type="text" class="form-control @error('serviceName') is-invalid @enderror">
            @error('serviceName')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Service Name In English</label>
            <input wire:model.defer="serviceEnglishName" type="text" class="form-control @error('serviceEnglishName') is-invalid @enderror">
            @error('serviceEnglishName')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">{{ __('Branch') }}</label>
            <select wire:model.defer="serviceBranch" class="form-select @error('serviceBranch') is-invalid @enderror">
              @foreach ($branches as $key => $branchName)
                <option value="{{ $key }}">{{ $branchName }}</option>
              @endforeach
            </select>
            @error('serviceBranch')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">{{ __('Price') }}</label>
            <input wire:model.defer="servicePrice" type="number" min="0" step="0.01" class="form-control @error('servicePrice') is-invalid @enderror">
            @error('servicePrice')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">ملاحظة السعر</label>
            <input wire:model.defer="servicePriceNote" type="text" class="form-control @error('servicePriceNote') is-invalid @enderror" placeholder="مثال: 150-400 أو متغير">
            @error('servicePriceNote')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-0">
            <label class="form-label">{{ __('Status') }}</label>
            <select wire:model.defer="serviceActive" class="form-select @error('serviceActive') is-invalid @enderror">
              <option value="1">{{ __('Active') }}</option>
              <option value="0">{{ __('Inactive') }}</option>
            </select>
            @error('serviceActive')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
          <button wire:click="submitService" type="button" class="btn btn-primary">{{ $isEdit ? __('Update Service') : __('Add New Service') }}</button>
        </div>
      </div>
    </div>
  </div>
</div>
