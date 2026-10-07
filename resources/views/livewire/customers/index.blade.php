<div>
  @section('title', 'Customers')

  <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
      <h4 class="mb-1">{{ __('Customers') }}</h4>
      <p class="text-muted mb-0">{{ __('Register customers and track every service they receive without creating a login account for them.') }}</p>
    </div>
    <button wire:click="showNewCustomerModal" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customerModal">
      <span class="ti ti-plus me-1"></span>{{ __('Add New Customer') }}
    </button>
  </div>

  <div class="row g-4">
    <div class="col-12 col-xl-5">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center gap-2">
          <h5 class="mb-0">{{ __('Customers List') }}</h5>
          <input wire:model.live="searchTerm" type="text" class="form-control w-auto" style="min-width: 220px;" placeholder="{{ __('Search customers...') }}">
        </div>
        <div class="card-body p-0">
          <div class="list-group list-group-flush">
            @forelse ($customers as $customer)
              <button type="button"
                wire:click="selectCustomer({{ $customer->id }})"
                class="list-group-item list-group-item-action border-0 text-start {{ $selectedCustomer?->id === $customer->id ? 'active' : '' }}">
                <div class="d-flex justify-content-between align-items-start gap-3">
                  <div>
                    <h6 class="mb-1 {{ $selectedCustomer?->id === $customer->id ? 'text-white' : '' }}">{{ $customer->name }}</h6>
                    @if ($customer->english_name)
                      <div class="small {{ $selectedCustomer?->id === $customer->id ? 'text-white-50' : 'text-muted' }}">{{ $customer->english_name }}</div>
                    @endif
                    <div class="small {{ $selectedCustomer?->id === $customer->id ? 'text-white-50' : 'text-muted' }}">{{ $customer->phone ?: __('No phone number') }}</div>
                    <div class="small {{ $selectedCustomer?->id === $customer->id ? 'text-white-50' : 'text-muted' }}">
                      {{ $customer->nationality ?: __('No nationality recorded') }}
                      @if ($customer->nationality_en)
                        <span dir="ltr"> / {{ $customer->nationality_en }}</span>
                      @endif
                    </div>
                  </div>
                  <span class="badge {{ $selectedCustomer?->id === $customer->id ? 'bg-white text-primary' : 'bg-label-primary' }}">
                    {{ $customer->services_count }} {{ __('Services') }}
                  </span>
                </div>
              </button>
            @empty
              <div class="p-4 text-center">
                <h5 class="mb-2">{{ __('No customers yet.') }}</h5>
                <p class="text-muted mb-0">{{ __('Start by adding the first customer record from the button above.') }}</p>
              </div>
            @endforelse
          </div>
        </div>
        <div class="card-footer">
          {{ $customers->links() }}
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-7">
      @if ($selectedCustomer)
        <div class="card mb-4">
          <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
              <h5 class="mb-1">{{ $selectedCustomer->name }}</h5>
              @if ($selectedCustomer->english_name)
                <div class="text-muted small" dir="ltr">{{ $selectedCustomer->english_name }}</div>
              @endif
              <div class="text-muted small">{{ __('Customer profile and service history') }}</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
              <button wire:click="showEditCustomerModal({{ $selectedCustomer->id }})" type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#customerModal">
                <span class="ti ti-pencil me-1"></span>{{ __('Edit') }}
              </button>
              <button wire:click="showNewServiceModal({{ $selectedCustomer->id }})" type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customerServiceModal">
                <span class="ti ti-plus me-1"></span>{{ __('Add Service') }}
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                  <div class="text-muted small mb-1">{{ __('Phone Number') }}</div>
                  <div>{{ $selectedCustomer->phone ?: __('No phone number') }}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                  <div class="text-muted small mb-1">{{ __('Nationality') }}</div>
                  <div>
                    {{ $selectedCustomer->nationality ?: __('No nationality recorded') }}
                    @if ($selectedCustomer->nationality_en)
                      <div class="text-muted small mt-1" dir="ltr">{{ $selectedCustomer->nationality_en }}</div>
                    @endif
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                  <div class="text-muted small mb-1">{{ __('Record Created At') }}</div>
                  <div>{{ $selectedCustomer->created_at?->translatedFormat('j F Y - h:i A') }}</div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                  <div class="text-muted small mb-1">{{ __('Total Services Used') }}</div>
                  <div>{{ $selectedCustomer->services->count() }}</div>
                </div>
              </div>
              <div class="col-12">
                <div class="border rounded p-3">
                  <div class="text-muted small mb-1">{{ __('Note') }}</div>
                  <div style="white-space: pre-wrap;">{{ $selectedCustomer->note ?: __('No notes added yet.') }}</div>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
              <h6 class="mb-0">{{ __('Service History') }}</h6>
              <div>
                @if ($confirmedCustomerId === $selectedCustomer->id)
                  <button wire:click="deleteCustomer({{ $selectedCustomer->id }})" type="button" class="btn btn-danger btn-sm">{{ __('Sure?') }}</button>
                @else
                  <button wire:click="confirmDeleteCustomer({{ $selectedCustomer->id }})" type="button" class="btn btn-outline-danger btn-sm">
                    <span class="ti ti-trash me-1"></span>{{ __('Delete') }}
                  </button>
                @endif
              </div>
            </div>

            @forelse ($selectedCustomer->services as $service)
              <div class="border rounded p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-2">
                  <div>
                    <h6 class="mb-1">{{ $service->service_name }}</h6>
                    <div class="small text-muted">
                      {{ __('Service provided at') }}: {{ $service->served_at?->translatedFormat('j F Y - h:i A') }}
                    </div>
                  </div>
                  <span class="badge bg-label-info">
                    {{ $service->employee?->full_name ?: $service->created_by }}
                  </span>
                </div>
                <div style="white-space: pre-wrap;">{{ $service->note ?: __('No service note.') }}</div>
              </div>
            @empty
              <div class="alert alert-warning mb-0">
                {{ __('No services have been recorded for this customer yet.') }}
              </div>
            @endforelse
          </div>
        </div>
      @else
        <div class="card">
          <div class="card-body text-center py-5">
            <h5 class="mb-2">{{ __('No customer selected') }}</h5>
            <p class="text-muted mb-0">{{ __('Add a customer first, then their profile and service history will appear here.') }}</p>
          </div>
        </div>
      @endif
    </div>
  </div>

  <div wire:ignore.self class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ $isEdit ? __('Update Customer') : __('Add New Customer') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">{{ __('Name') }}</label>
            <input wire:model.defer="customerName" type="text" class="form-control @error('customerName') is-invalid @enderror">
            @error('customerName')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">{{ __('Name In English') }}</label>
            <input wire:model.defer="customerEnglishName" type="text" dir="ltr" class="form-control @error('customerEnglishName') is-invalid @enderror">
            @error('customerEnglishName')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">{{ __('Phone Number') }}</label>
            <input wire:model.defer="customerPhone" type="text" class="form-control @error('customerPhone') is-invalid @enderror">
            @error('customerPhone')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">{{ __('Nationality') }}</label>
            <input wire:model.defer="customerNationality" type="text" class="form-control @error('customerNationality') is-invalid @enderror">
            @error('customerNationality')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">{{ __('Nationality In English') }}</label>
            <input wire:model.defer="customerNationalityEn" type="text" dir="ltr" class="form-control @error('customerNationalityEn') is-invalid @enderror">
            @error('customerNationalityEn')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-0">
            <label class="form-label">{{ __('Note') }}</label>
            <textarea wire:model.defer="customerNote" rows="4" class="form-control @error('customerNote') is-invalid @enderror"></textarea>
            @error('customerNote')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
          <button wire:click="submitCustomer" type="button" class="btn btn-primary">{{ $isEdit ? __('Update Customer') : __('Add New Customer') }}</button>
        </div>
      </div>
    </div>
  </div>

  <div wire:ignore.self class="modal fade" id="customerServiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ __('Add Service') }}</h5>
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
          <div class="mb-0">
            <label class="form-label">{{ __('Note') }}</label>
            <textarea wire:model.defer="serviceNote" rows="4" class="form-control @error('serviceNote') is-invalid @enderror"></textarea>
            @error('serviceNote')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
          <button wire:click="submitService" type="button" class="btn btn-primary">{{ __('Save Service') }}</button>
        </div>
      </div>
    </div>
  </div>
</div>
