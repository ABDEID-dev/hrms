<div dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
  @section('title', __('ui.dye_reports'))

  @section('page-style')
    <style>
      .dye-reports .number-cell { direction: ltr; text-align: center; white-space: nowrap; }
      .dye-reports .summary-box {
        height: 100%; padding: .85rem 1rem; border: 1px solid rgba(var(--bs-border-color-rgb), .5);
        border-radius: .6rem; background: rgba(var(--bs-body-bg-rgb), .25);
      }
      .dye-reports .summary-value { direction: ltr; font-size: 1.2rem; font-weight: 700; }
      .dye-reports .mobile-report-list { display: none; }
      .dye-reports .mobile-report-card {
        padding: 1rem; border: 1px solid rgba(var(--bs-border-color-rgb), .5);
        border-radius: .7rem; background: rgba(var(--bs-body-bg-rgb), .25);
      }
      .dye-reports .stock-flow {
        display: grid; grid-template-columns: 1fr auto 1fr; align-items: center;
        gap: .6rem; margin-top: .75rem; padding: .7rem; border-radius: .55rem;
        background: rgba(var(--bs-body-bg-rgb), .4);
      }
      @media (max-width: 767.98px) {
        .dye-reports { margin-inline: -.75rem; }
        .dye-reports .card { border-radius: 0; }
        .dye-reports .filter-actions .btn { width: 100%; }
        .dye-reports .desktop-report-table { display: none; }
        .dye-reports .mobile-report-list { display: flex; flex-direction: column; gap: .8rem; padding: 1rem; }
      }
    </style>
  @endsection

  <div class="dye-reports">
    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h5 class="mb-1">{{ __('ui.dye_reports') }}</h5>
        <small class="text-muted">{{ __('ui.dye_reports_hint') }}</small>
      </div>
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-xl-3 col-md-6">
            <label class="form-label">{{ __('ui.search_employee_or_dye') }}</label>
            <input wire:model.live.debounce.300ms="search" class="form-control" placeholder="{{ __('ui.search_employee_or_dye') }}">
          </div>
          <div class="col-xl-2 col-md-6">
            <label class="form-label">{{ __('ui.employee_name') }}</label>
            <select wire:model.live="employeeId" class="form-select">
              <option value="">{{ __('ui.all_employees') }}</option>
              @foreach($employees as $employee)
                <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-xl-2 col-md-6">
            <label class="form-label">{{ __('ui.dye') }}</label>
            <select wire:model.live="dyeId" class="form-select">
              <option value="">{{ __('ui.all_dyes') }}</option>
              @foreach($dyes as $dye)
                <option value="{{ $dye->id }}">{{ $dye->code }} - {{ $dye->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-xl-2 col-md-6">
            <label class="form-label">{{ __('ui.from_date') }}</label>
            <input wire:model.live="dateFrom" type="date" class="form-control">
          </div>
          <div class="col-xl-2 col-md-6">
            <label class="form-label">{{ __('ui.to_date') }}</label>
            <input wire:model.live="dateTo" type="date" class="form-control">
          </div>
          <div class="col-xl-1 col-md-6 filter-actions">
            <button wire:click="resetFilters" type="button" class="btn btn-label-secondary">
              <i class="ti ti-refresh"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="summary-box">
          <div class="text-muted small">{{ __('ui.deduction_records') }}</div>
          <div class="summary-value">{{ number_format($summary->deductions_count ?? 0) }}</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="summary-box">
          <div class="text-muted small">{{ __('ui.total_deducted_dyes') }}</div>
          <div class="summary-value text-danger">{{ number_format($summary->deducted_quantity ?? 0) }}</div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="summary-box">
          <div class="text-muted small">{{ __('ui.current_dye_stock') }}</div>
          <div class="summary-value text-primary">{{ number_format($currentStock) }}</div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="table-responsive desktop-report-table">
        <table class="table align-middle mb-0">
          <thead>
            <tr>
              <th>{{ __('ui.deduction_time') }}</th>
              <th>{{ __('ui.employee_name') }}</th>
              <th>{{ __('ui.dye') }}</th>
              <th class="text-center">{{ __('ui.stock_before') }}</th>
              <th class="text-center">{{ __('ui.deducted') }}</th>
              <th class="text-center">{{ __('ui.stock_remaining') }}</th>
              <th>{{ __('ui.note') }}</th>
              <th>{{ __('ui.recorded_by') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($items as $item)
              <tr>
                <td class="number-cell">
                  <div>{{ $item->revenue?->date?->format('d-m-Y') }}</div>
                  <small class="text-muted">{{ $item->updated_at?->timezone('Asia/Dubai')->format('h:i A') }}</small>
                </td>
                <td class="fw-semibold">{{ $item->revenue?->employee?->full_name }}</td>
                <td>{{ $item->dye?->code }} - {{ $item->dye?->name }}</td>
                <td class="number-cell">{{ number_format($item->stock_before) }}</td>
                <td class="number-cell text-danger fw-bold">-{{ number_format($item->quantity) }}</td>
                <td class="number-cell text-primary fw-bold">{{ number_format($item->stock_after) }}</td>
                <td>{{ $item->revenue?->note ?: '---' }}</td>
                <td>{{ $item->updated_by }}</td>
              </tr>
            @empty
              <tr><td colspan="8" class="text-center text-muted py-5">{{ __('ui.no_dye_report_records') }}</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mobile-report-list">
        @forelse($items as $item)
          <div class="mobile-report-card">
            <div class="d-flex justify-content-between gap-3">
              <div>
                <div class="fw-bold">{{ $item->revenue?->employee?->full_name }}</div>
                <small class="text-muted">{{ $item->revenue?->date?->format('d-m-Y') }} {{ $item->updated_at?->timezone('Asia/Dubai')->format('h:i A') }}</small>
              </div>
              <span class="badge bg-label-primary">{{ $item->dye?->code }} - {{ $item->dye?->name }}</span>
            </div>
            <div class="stock-flow">
              <div class="text-center">
                <small class="text-muted d-block">{{ __('ui.stock_before') }}</small>
                <strong>{{ $item->stock_before }}</strong>
              </div>
              <div class="text-danger fw-bold">- {{ $item->quantity }}</div>
              <div class="text-center">
                <small class="text-muted d-block">{{ __('ui.stock_remaining') }}</small>
                <strong class="text-primary">{{ $item->stock_after }}</strong>
              </div>
            </div>
            @if($item->revenue?->note)
              <div class="small mt-2"><span class="text-muted">{{ __('ui.note') }}:</span> {{ $item->revenue->note }}</div>
            @endif
            <div class="small mt-2"><span class="text-muted">{{ __('ui.recorded_by') }}:</span> {{ $item->updated_by }}</div>
          </div>
        @empty
          <div class="text-center text-muted py-4">{{ __('ui.no_dye_report_records') }}</div>
        @endforelse
      </div>

      <div class="card-body border-top">{{ $items->links() }}</div>
    </div>
  </div>
</div>
