<div dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
  @section('title', __('ui.dye_salon_revenues'))

  @section('page-style')
    <style>
      .dye-revenues .number-cell { direction: ltr; text-align: center; white-space: nowrap; }
      .dye-revenues .form-card { overflow: hidden; }
      .dye-revenues .form-hero {
        display: flex; align-items: center; gap: .75rem; padding: .85rem 1.25rem;
        background: rgba(105, 108, 255, .08);
      }
      .dye-revenues .form-hero-icon {
        display: grid; place-items: center; width: 36px; height: 36px; flex: 0 0 36px;
        border-radius: .5rem; color: var(--bs-primary); background: rgba(105, 108, 255, .14); font-size: 1.15rem;
      }
      .dye-revenues .form-hero h5 { font-size: 1rem; }
      .dye-revenues .form-hero .text-muted { font-size: .82rem; }
      .dye-revenues .form-section { padding: .9rem 1.25rem; }
      .dye-revenues .form-section + .form-section { border-top: 1px solid rgba(var(--bs-border-color-rgb), .55); }
      .dye-revenues .section-heading { display: flex; align-items: center; gap: .4rem; margin-bottom: .7rem; font-size: .9rem; font-weight: 700; }
      .dye-revenues .section-step {
        width: 4px; height: 18px; border-radius: 1rem; overflow: hidden;
        color: transparent; background: var(--bs-primary); font-size: 0;
      }
      .dye-revenues .form-label { margin-bottom: .3rem; font-size: .82rem; }
      .dye-revenues .form-control, .dye-revenues .form-select { min-height: 40px; padding-block: .4rem; }
      .dye-revenues .dye-item-row {
        border: 1px solid rgba(var(--bs-border-color-rgb), .55); border-radius: .75rem;
        padding: .7rem .85rem; background: rgba(var(--bs-body-bg-rgb), .22);
      }
      .dye-revenues .dye-item-number {
        display: inline-flex; align-items: center; gap: .35rem; margin-bottom: .45rem;
        color: var(--bs-primary); font-size: .78rem; font-weight: 700;
      }
      .dye-revenues .dye-badge {
        display: inline-flex; align-items: center; gap: .35rem; margin: .15rem;
        font-size: .8rem; white-space: normal;
      }
      .dye-revenues .form-actions { display: flex; justify-content: flex-start; }
      .dye-revenues .save-button { min-width: 210px; }
      .dye-revenues .notes-input { min-height: 64px; resize: vertical; }
      .dye-revenues .mobile-revenue-list { display: none; }
      .dye-revenues .mobile-revenue-card {
        border: 1px solid rgba(var(--bs-border-color-rgb), .55); border-radius: .75rem;
        padding: 1rem; background: rgba(var(--bs-body-bg-rgb), .28);
      }
      .dye-revenues .mobile-revenue-line {
        display: flex; align-items: flex-start; justify-content: space-between;
        gap: 1rem; padding: .55rem 0;
      }
      .dye-revenues .mobile-revenue-line + .mobile-revenue-line {
        border-top: 1px dashed rgba(var(--bs-border-color-rgb), .55);
      }

      @media (max-width: 767.98px) {
        .dye-revenues { margin-inline: -.75rem; }
        .dye-revenues .card { border-radius: 0; }
        .dye-revenues .form-hero { align-items: flex-start; padding: .8rem; }
        .dye-revenues .form-hero h5 { font-size: .95rem; }
        .dye-revenues .form-hero .text-muted { line-height: 1.4; }
        .dye-revenues .form-section { padding: .8rem; }
        .dye-revenues .section-heading { margin-bottom: .6rem; }
        .dye-revenues .dye-section-header { align-items: stretch !important; flex-direction: column; }
        .dye-revenues .dye-section-header .btn { width: 100%; min-height: 44px; }
        .dye-revenues .dye-item-row { padding: .7rem; }
        .dye-revenues .remove-item-wrap { text-align: stretch !important; }
        .dye-revenues .remove-item-wrap .btn { width: 100%; min-height: 42px; }
        .dye-revenues .form-actions {
          position: sticky; bottom: 0; z-index: 5; margin: .75rem -.8rem -.8rem;
          padding: .65rem .8rem; background: var(--bs-card-bg);
          border-top: 1px solid rgba(var(--bs-border-color-rgb), .55);
        }
        .dye-revenues .save-button { width: 100%; min-width: 0; min-height: 44px; }
        .dye-revenues .list-card-header { align-items: stretch !important; flex-direction: column; }
        .dye-revenues .list-card-header .form-control { max-width: none !important; width: 100%; }
        .dye-revenues .desktop-revenue-table { display: none; }
        .dye-revenues .mobile-revenue-list { display: flex; flex-direction: column; gap: .85rem; padding: 1rem; }
        .dye-revenues .dye-badge { display: flex; width: 100%; justify-content: space-between; margin: .2rem 0; }
      }
    </style>
  @endsection

  <div class="dye-revenues">
    @if($this->canOperate())
      <div class="card form-card mb-4" id="dyeRevenueForm">
        <div class="form-hero">
          <div class="form-hero-icon"><i class="ti ti-color-swatch"></i></div>
          <div>
            <h5 class="mb-1">{{ $editingRevenueId ? __('ui.edit_dye_salon_revenue') : __('ui.add_dye_salon_revenue') }}</h5>
            <div class="text-muted">{{ __('ui.dye_salon_revenue_hint') }}</div>
          </div>
        </div>

        <div class="form-section">
          <div class="section-heading">
            <span class="section-step">1</span>
            <span>{{ __('ui.employee_and_date') }}</span>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">{{ __('ui.employee_name') }}</label>
              <select wire:model.defer="form.employee_id" class="form-select @error('form.employee_id') is-invalid @enderror">
                <option value="">{{ __('ui.choose_employee') }}</option>
                @foreach($employees as $employee)
                  <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                @endforeach
              </select>
              @error('form.employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label">{{ __('ui.date') }}</label>
              <input wire:model.defer="form.date" type="date" max="{{ now('Asia/Dubai')->toDateString() }}" class="form-control @error('form.date') is-invalid @enderror">
              @error('form.date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          </div>
        </div>

        <div class="form-section">
          <div class="dye-section-header d-flex justify-content-between align-items-center gap-3 mb-3">
            <div class="section-heading mb-0">
              <span class="section-step">2</span>
              <span>{{ __('ui.used_dyes') }}</span>
            </div>
            <button wire:click="addItem" type="button" class="btn btn-sm btn-label-primary">
              <i class="ti ti-plus me-1"></i>{{ __('ui.add_another_dye') }}
            </button>
          </div>

          <div class="d-flex flex-column gap-3">
            @foreach($form['items'] as $index => $item)
              <div class="dye-item-row" wire:key="dye-revenue-item-{{ $index }}">
                <div class="dye-item-number">
                  <i class="ti ti-droplet"></i>
                  {{ __('ui.dye_item_number', ['number' => $index + 1]) }}
                </div>
                <div class="row g-3 align-items-end">
                  <div class="col-lg-7 col-md-6">
                    <label class="form-label">{{ __('ui.dye_name') }}</label>
                    <select wire:model.defer="form.items.{{ $index }}.dye_id" class="form-select @error('form.items.'.$index.'.dye_id') is-invalid @enderror">
                      <option value="">{{ __('ui.choose_dye') }}</option>
                      @foreach($dyes as $dye)
                        <option value="{{ $dye->id }}">{{ $dye->code }} - {{ $dye->name }} ({{ __('ui.available') }}: {{ $dye->warehouse_stock }})</option>
                      @endforeach
                    </select>
                    @error('form.items.'.$index.'.dye_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                  <div class="col-lg-3 col-md-4">
                    <label class="form-label">{{ __('ui.quantity') }}</label>
                    <input wire:model.defer="form.items.{{ $index }}.quantity" type="number" min="1" class="form-control number-cell @error('form.items.'.$index.'.quantity') is-invalid @enderror">
                    @error('form.items.'.$index.'.quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                  <div class="col-lg-2 col-md-2 text-center remove-item-wrap">
                    <button wire:click="removeItem({{ $index }})" type="button" class="btn btn-label-danger" @disabled(count($form['items']) === 1)>
                      <i class="ti ti-trash me-1"></i><span class="d-md-none d-lg-inline">{{ __('ui.remove') }}</span>
                    </button>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        <div class="form-section">
          <div class="section-heading">
            <span class="section-step">3</span>
            <span>{{ __('ui.notes_and_save') }}</span>
          </div>
          <label class="form-label">{{ __('ui.note') }}</label>
          <textarea wire:model.defer="form.note" class="form-control notes-input @error('form.note') is-invalid @enderror" rows="2"></textarea>
          @error('form.note')<div class="invalid-feedback">{{ $message }}</div>@enderror

          <div class="form-actions mt-4">
            <button wire:click="save" wire:loading.attr="disabled" type="button" class="btn btn-primary save-button">
              <i class="ti ti-device-floppy me-1"></i>
              {{ $editingRevenueId ? __('ui.save_changes_and_update_stock') : __('ui.save_and_deduct_stock') }}
            </button>
            @if($editingRevenueId)
              <button wire:click="cancelEdit" type="button" class="btn btn-label-secondary ms-2">
                {{ __('ui.cancel_edit') }}
              </button>
            @endif
          </div>
        </div>
      </div>
    @endif

    <div class="card">
      <div class="card-header list-card-header border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
          <h5 class="mb-1">{{ __('ui.dye_salon_revenues') }}</h5>
          <small class="text-muted">{{ __('ui.dye_salon_revenues_list_hint') }}</small>
        </div>
        <input wire:model.live.debounce.300ms="search" class="form-control" style="max-width: 320px" placeholder="{{ __('ui.search_employee_or_dye') }}">
      </div>

      <div class="table-responsive desktop-revenue-table">
        <table class="table align-middle mb-0">
          <thead>
            <tr>
              <th>{{ __('ui.date') }}</th>
              <th>{{ __('ui.employee_name') }}</th>
              <th class="text-center">{{ __('ui.dyes_count') }}</th>
              <th>{{ __('ui.used_dyes') }}</th>
              <th>{{ __('ui.note') }}</th>
              <th>{{ __('ui.recorded_by') }}</th>
              @if($this->canAdminister())
                <th class="text-center">{{ __('ui.actions') }}</th>
              @endif
            </tr>
          </thead>
          <tbody>
            @forelse($revenues as $revenue)
              <tr>
                <td class="number-cell">{{ $revenue->date?->format('d-m-Y') }}</td>
                <td class="fw-semibold">{{ $revenue->employee?->full_name }}</td>
                <td class="number-cell fw-bold">{{ number_format($revenue->total_quantity) }}</td>
                <td>
                  @foreach($revenue->items as $item)
                    <span class="badge bg-label-primary dye-badge">
                      <span>{{ $item->dye?->code }} - {{ $item->dye?->name }}</span>
                      <strong>&times; {{ $item->quantity }}</strong>
                    </span>
                  @endforeach
                </td>
                <td>{{ $revenue->note ?: '---' }}</td>
                <td>{{ $revenue->created_by }}</td>
                @if($this->canAdminister())
                  <td class="text-center">
                    <div class="d-inline-flex gap-1">
                      <button wire:click="editRevenue({{ $revenue->id }})" type="button" class="btn btn-sm btn-icon btn-label-info" title="{{ __('ui.edit') }}">
                        <i class="ti ti-pencil"></i>
                      </button>
                      <button wire:click="confirmDeleteRevenue({{ $revenue->id }})" type="button" class="btn btn-sm btn-icon btn-label-danger" title="{{ __('Delete') }}">
                        <i class="ti ti-trash"></i>
                      </button>
                    </div>
                    @if($confirmedRevenueId === $revenue->id)
                      <button wire:click="deleteRevenue({{ $revenue->id }})" type="button" class="btn btn-xs btn-danger mt-1">
                        {{ __('ui.confirm_delete_and_restore_stock') }}
                      </button>
                    @endif
                  </td>
                @endif
              </tr>
            @empty
              <tr><td colspan="{{ $this->canAdminister() ? 7 : 6 }}" class="text-center text-muted py-5">{{ __('ui.no_dye_revenues_yet') }}</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mobile-revenue-list">
        @forelse($revenues as $revenue)
          <div class="mobile-revenue-card">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
              <div>
                <div class="fw-bold">{{ $revenue->employee?->full_name }}</div>
                <small class="text-muted">{{ $revenue->date?->format('d-m-Y') }}</small>
              </div>
              <span class="badge bg-label-success">{{ $revenue->total_quantity }} {{ __('ui.dye_units') }}</span>
            </div>
            <div class="mobile-revenue-line">
              <span class="text-muted">{{ __('ui.used_dyes') }}</span>
              <div class="flex-grow-1">
                @foreach($revenue->items as $item)
                  <span class="badge bg-label-primary dye-badge">
                    <span>{{ $item->dye?->code }} - {{ $item->dye?->name }}</span>
                    <strong>&times; {{ $item->quantity }}</strong>
                  </span>
                @endforeach
              </div>
            </div>
            @if($revenue->note)
              <div class="mobile-revenue-line">
                <span class="text-muted">{{ __('ui.note') }}</span>
                <span>{{ $revenue->note }}</span>
              </div>
            @endif
            <div class="mobile-revenue-line">
              <span class="text-muted">{{ __('ui.recorded_by') }}</span>
              <span>{{ $revenue->created_by }}</span>
            </div>
            @if($this->canAdminister())
              <div class="d-flex gap-2 pt-2">
                <button wire:click="editRevenue({{ $revenue->id }})" type="button" class="btn btn-sm btn-label-info flex-grow-1">
                  <i class="ti ti-pencil me-1"></i>{{ __('ui.edit') }}
                </button>
                <button wire:click="confirmDeleteRevenue({{ $revenue->id }})" type="button" class="btn btn-sm btn-label-danger flex-grow-1">
                  <i class="ti ti-trash me-1"></i>{{ __('Delete') }}
                </button>
              </div>
              @if($confirmedRevenueId === $revenue->id)
                <button wire:click="deleteRevenue({{ $revenue->id }})" type="button" class="btn btn-danger btn-sm w-100 mt-2">
                  {{ __('ui.confirm_delete_and_restore_stock') }}
                </button>
              @endif
            @endif
          </div>
        @empty
          <div class="text-center text-muted py-4">{{ __('ui.no_dye_revenues_yet') }}</div>
        @endforelse
      </div>

      <div class="card-body border-top">{{ $revenues->links() }}</div>
    </div>
  </div>
</div>

@push('custom-scripts')
  <script>
    window.addEventListener('scrollToDyeRevenueForm', () => {
      document.getElementById('dyeRevenueForm')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  </script>
@endpush
