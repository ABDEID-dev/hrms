<div dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
  @section('title', __('ui.maktoom_dye_management'))

  @section('page-style')
    <style>
      .dye-inventory .summary-card {
        border: 1px solid rgba(var(--bs-border-color-rgb), .55);
        border-radius: .65rem;
        padding: 1rem;
        height: 100%;
      }

      .dye-inventory .summary-number {
        direction: ltr;
        font-size: 1.35rem;
        font-weight: 700;
      }

      .dye-inventory .number-cell {
        direction: ltr;
        text-align: center;
        white-space: nowrap;
      }

      .dye-inventory .dye-table th {
        background: #2f5d1b;
        color: #fff;
        white-space: nowrap;
      }

      @media (max-width: 767.98px) {
        .dye-inventory {
          margin-inline: -.75rem;
        }

        .dye-inventory .card {
          border-radius: 0;
        }
      }
    </style>
  @endsection

  <div class="dye-inventory">
    <div class="card mb-4">
      <div class="card-header border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
          <h5 class="mb-1">{{ __('ui.maktoom_dye_management') }}</h5>
          <small class="text-muted">{{ __('ui.simple_dye_inventory_hint') }}</small>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <input wire:model.live.debounce.300ms="search" class="form-control" placeholder="{{ __('ui.search_dyes') }}">
          @if($this->canOperate())
            <button wire:click="showNewDyeModal" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#maktoomDyeModal">
              <i class="ti ti-plus me-1"></i>{{ __('ui.add_dye') }}
            </button>
          @endif
        </div>
      </div>

      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="summary-card">
              <div class="text-muted small">{{ __('ui.dye_types_count') }}</div>
              <div class="summary-number">{{ number_format($summary->dyes_count) }}</div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="summary-card">
              <div class="text-muted small">{{ __('ui.current_dye_stock') }}</div>
              <div class="summary-number text-primary">{{ number_format($summary->warehouse_stock) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="table-responsive">
        <table class="table dye-table align-middle mb-0">
          <thead>
            <tr>
              <th class="text-center">{{ __('ui.dye_number') }}</th>
              <th class="text-center">{{ __('ui.dye_name') }}</th>
              <th class="text-center">{{ __('ui.current_stock') }}</th>
              <th class="text-center">{{ __('ui.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($dyes as $dye)
              <tr>
                <td class="number-cell fw-semibold">{{ $dye->code }}</td>
                <td class="text-center fw-semibold">{{ $dye->name }}</td>
                <td class="number-cell fw-bold">{{ number_format($dye->warehouse_stock) }}</td>
                <td class="text-center">
                  @if($this->canOperate())
                    <button wire:click="showMovementModal({{ $dye->id }})" class="btn btn-sm btn-label-success" data-bs-toggle="modal" data-bs-target="#maktoomDyeMovementModal">
                      <i class="ti ti-plus me-1"></i>{{ __('ui.add_quantity') }}
                    </button>
                  @endif
                  @if($this->canManage())
                    <button wire:click="showEditDyeModal({{ $dye->id }})" class="btn btn-sm btn-icon btn-label-info" data-bs-toggle="modal" data-bs-target="#maktoomDyeModal" title="{{ __('ui.edit') }}">
                      <i class="ti ti-pencil"></i>
                    </button>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-muted py-5">{{ __('ui.no_dyes_yet') }}</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="card-body border-top">{{ $dyes->links() }}</div>
    </div>

    <div class="card">
      <div class="card-header border-bottom">
        <h5 class="mb-0">{{ __('ui.dye_stock_entries') }}</h5>
      </div>
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr>
              <th>{{ __('ui.date') }}</th>
              <th>{{ __('ui.dye') }}</th>
              <th class="text-center">{{ __('ui.added_quantity') }}</th>
              <th class="text-center">{{ __('ui.balance_after') }}</th>
              <th>{{ __('ui.employee') }}</th>
              <th>{{ __('ui.note') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($movements as $movement)
              <tr>
                <td class="number-cell">{{ $movement->occurred_at?->timezone('Asia/Dubai')->format('d-m-Y h:i A') }}</td>
                <td>{{ $movement->dye?->code }} - {{ $movement->dye?->name }}</td>
                <td class="number-cell text-success">+{{ number_format($movement->quantity) }}</td>
                <td class="number-cell fw-semibold">{{ number_format($movement->warehouse_balance_after) }}</td>
                <td>{{ $movement->created_by }}</td>
                <td>{{ $movement->note ?: '---' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-4">{{ __('ui.no_movements_yet') }}</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div wire:ignore.self class="modal fade" id="maktoomDyeModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ $editingDyeId ? __('ui.edit_dye') : __('ui.add_dye') }}</h5>
            <button class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">{{ __('ui.dye_number') }}</label>
                <input wire:model.defer="dyeForm.code" class="form-control @error('dyeForm.code') is-invalid @enderror">
                @error('dyeForm.code')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ __('ui.dye_name') }}</label>
                <input wire:model.defer="dyeForm.name" class="form-control @error('dyeForm.name') is-invalid @enderror" placeholder="10/00">
                @error('dyeForm.name')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              @if(! $editingDyeId)
                <div class="col-12">
                  <label class="form-label">{{ __('ui.opening_dye_quantity') }}</label>
                  <input wire:model.defer="dyeForm.warehouse_stock" type="number" min="0" class="form-control number-cell @error('dyeForm.warehouse_stock') is-invalid @enderror">
                  @error('dyeForm.warehouse_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              @endif
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('ui.cancel') }}</button>
            <button wire:click="saveDye" class="btn btn-primary">{{ __('ui.save') }}</button>
          </div>
        </div>
      </div>
    </div>

    <div wire:ignore.self class="modal fade" id="maktoomDyeMovementModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ __('ui.add_dye_stock') }}</h5>
            <button class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">{{ __('ui.quantity') }}</label>
                <input wire:model.defer="movementForm.quantity" type="number" min="1" class="form-control number-cell @error('movementForm.quantity') is-invalid @enderror">
                @error('movementForm.quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <label class="form-label">{{ __('ui.note_optional') }}</label>
                <textarea wire:model.defer="movementForm.note" class="form-control"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('ui.cancel') }}</button>
            <button wire:click="saveMovement" class="btn btn-primary">{{ __('ui.save') }}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
