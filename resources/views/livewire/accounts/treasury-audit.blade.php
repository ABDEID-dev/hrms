<div dir="rtl">
  @section('title', __('ui.treasury_audit'))

  @section('page-style')
    <style>
      .treasury-audit-page .summary-card {
        border: 1px solid rgba(var(--bs-border-color-rgb), .42);
        border-radius: .65rem;
        background: rgba(var(--bs-body-bg-rgb), .42);
        padding: 1rem;
      }

      .treasury-audit-page .summary-label {
        color: var(--bs-secondary-color);
        font-size: .85rem;
      }

      .treasury-audit-page .money,
      .treasury-audit-page .summary-value {
        direction: ltr;
        font-weight: 800;
        white-space: nowrap;
      }

      .treasury-audit-page input[type="date"],
      .treasury-audit-page .date-text {
        direction: ltr;
        unicode-bidi: isolate;
        text-align: center;
      }

      .treasury-audit-page .date-text {
        display: inline-block;
      }

      .treasury-audit-page .report-table th {
        background: #21445b;
        color: #fff;
        white-space: nowrap;
        vertical-align: middle;
      }

      .treasury-audit-page details summary {
        cursor: pointer;
        list-style: none;
      }

      .treasury-audit-page details summary::-webkit-details-marker {
        display: none;
      }

      .treasury-audit-page .audit-box {
        border: 1px solid rgba(var(--bs-border-color-rgb), .35);
        border-radius: .65rem;
        background: rgba(var(--bs-body-bg-rgb), .5);
        padding: .85rem;
      }

      .treasury-audit-page .audit-card {
        border: 1px solid rgba(var(--bs-border-color-rgb), .35);
        border-radius: .8rem;
        background: rgba(var(--bs-body-bg-rgb), .5);
        overflow: hidden;
      }

      .treasury-audit-page .audit-summary {
        padding: 1rem;
      }

      .treasury-audit-page .audit-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        color: var(--bs-secondary-color);
        font-size: .82rem;
      }

      .treasury-audit-page .audit-pill {
        border: 1px solid rgba(var(--bs-border-color-rgb), .35);
        border-radius: .55rem;
        padding: .45rem .6rem;
        background: rgba(var(--bs-body-bg-rgb), .38);
      }

      .treasury-audit-page .audit-detail-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .65rem;
      }

      .treasury-audit-page .audit-detail-item,
      .treasury-audit-page .audit-change-item {
        border: 1px solid rgba(var(--bs-border-color-rgb), .28);
        border-radius: .6rem;
        padding: .7rem;
        background: rgba(var(--bs-body-bg-rgb), .35);
      }

      .treasury-audit-page .audit-change-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .65rem;
      }

      .treasury-audit-page .audit-cash-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .65rem;
      }

      .treasury-audit-page .audit-value {
        overflow-wrap: anywhere;
      }

      .treasury-audit-page .daily-card {
        border: 1px solid rgba(var(--bs-border-color-rgb), .38);
        border-radius: .75rem;
        background: rgba(var(--bs-body-bg-rgb), .45);
        padding: .9rem;
      }

      .treasury-audit-page .daily-line {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        padding: .45rem 0;
        border-bottom: 1px solid rgba(var(--bs-border-color-rgb), .25);
      }

      .treasury-audit-page .daily-line:last-child {
        border-bottom: 0;
      }

      .treasury-audit-page .diff-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
      }

      .treasury-audit-page pre {
        margin: 0;
        white-space: pre-wrap;
        word-break: break-word;
        direction: ltr;
        text-align: left;
        font-size: .78rem;
      }

      @media (max-width: 767.98px) {
        .treasury-audit-page {
          margin-inline: -.75rem;
        }

        .treasury-audit-page .card {
          border-radius: 0;
        }

        .treasury-audit-page .card-header > .d-flex,
        .treasury-audit-page .card-header .d-flex {
          width: 100%;
        }

        .treasury-audit-page .card-header input,
        .treasury-audit-page .card-header select,
        .treasury-audit-page .card-header button {
          width: 100%;
        }

        .treasury-audit-page .diff-grid {
          grid-template-columns: 1fr;
        }

        .treasury-audit-page .audit-summary {
          padding: .85rem;
        }

        .treasury-audit-page .audit-detail-grid,
        .treasury-audit-page .audit-change-grid,
        .treasury-audit-page .audit-cash-grid {
          grid-template-columns: 1fr;
        }

        .treasury-audit-page .audit-meta {
          display: grid;
          grid-template-columns: 1fr 1fr;
          gap: .45rem;
        }
      }
    </style>
  @endsection

  <div class="treasury-audit-page">
    <div class="card mb-4">
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
          <h5 class="mb-1">{{ __('ui.treasury_audit') }}</h5>
          <small class="text-muted">{{ __('ui.treasury_audit_hint') }}</small>
        </div>

        <div class="d-flex flex-wrap gap-2">
          <select wire:model.defer="account" class="form-select" style="min-width: 150px;">
            @foreach($accountLabels as $key => $label)
              <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
          </select>
          <input wire:model.defer="fromDate" type="date" dir="ltr" lang="en" class="form-control">
          <input wire:model.defer="toDate" type="date" dir="ltr" lang="en" class="form-control">
          <button wire:click="applyFilters" type="button" class="btn btn-primary">
            <i class="ti ti-filter me-1"></i>{{ __('ui.show_report') }}
          </button>
          <button wire:click="resetToThisMonth" type="button" class="btn btn-outline-secondary">
            {{ __('ui.this_month') }}
          </button>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="summary-card">
          <div class="summary-label">{{ __('ui.total_revenues') }}</div>
          <div class="summary-value text-success">AED {{ number_format($totals['revenues'], 2) }}</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="summary-card">
          <div class="summary-label">{{ __('ui.cash_revenues') }}</div>
          <div class="summary-value text-success">AED {{ number_format($totals['cash_revenues'], 2) }}</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="summary-card">
          <div class="summary-label">{{ __('ui.visa_revenues') }}</div>
          <div class="summary-value text-info">AED {{ number_format($totals['visa_revenues'], 2) }}</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="summary-card">
          <div class="summary-label">{{ __('ui.total_expenses') }}</div>
          <div class="summary-value text-danger">AED {{ number_format($totals['expenses'], 2) }}</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="summary-card">
          <div class="summary-label">{{ __('ui.purchases') }}</div>
          <div class="summary-value text-danger">AED {{ number_format($totals['purchases'], 2) }}</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="summary-card">
          <div class="summary-label">{{ __('ui.cash_withdrawals') }}</div>
          <div class="summary-value text-warning">AED {{ number_format($totals['cash_withdrawals'], 2) }}</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="summary-card">
          <div class="summary-label">{{ __('ui.tips') }}</div>
          <div class="summary-value text-warning">AED {{ number_format($totals['tips'], 2) }}</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="summary-card">
          <div class="summary-label">{{ __('ui.closing_cash_balance') }}</div>
          <div class="summary-value text-success">AED {{ number_format($totals['closing_cash'], 2) }}</div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-1">{{ __('ui.daily_treasury_totals') }}</h5>
        <small class="text-muted date-text">{{ $fromDate }} - {{ $toDate }}</small>
      </div>
      <div class="table-responsive d-none d-md-block">
        <table class="table table-hover report-table daily-table mb-0">
          <thead>
            <tr>
              <th>{{ __('ui.date') }}</th>
              <th>{{ __('ui.opening_cash_balance') }}</th>
              <th>{{ __('ui.total_revenues') }}</th>
              <th>{{ __('ui.cash_revenues') }}</th>
              <th>{{ __('ui.visa_revenues') }}</th>
              <th>{{ __('ui.total_expenses') }}</th>
              <th>{{ __('ui.purchases') }}</th>
              <th>{{ __('ui.cash_withdrawals') }}</th>
              <th>{{ __('ui.tips') }}</th>
              <th>{{ __('ui.net_cash_movement') }}</th>
              <th>{{ __('ui.closing_cash_balance') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($dailyRows as $row)
              <tr>
                <td class="fw-semibold date-text">{{ $row['date'] }}</td>
                <td class="money">AED {{ number_format($row['opening_cash'], 2) }}</td>
                <td class="money text-success">AED {{ number_format($row['revenues'], 2) }}</td>
                <td class="money text-success">AED {{ number_format($row['cash_revenues'], 2) }}</td>
                <td class="money text-info">AED {{ number_format($row['visa_revenues'], 2) }}</td>
                <td class="money text-danger">AED {{ number_format($row['expenses'], 2) }}</td>
                <td class="money text-danger">AED {{ number_format($row['purchases'], 2) }}</td>
                <td class="money text-warning">AED {{ number_format($row['cash_withdrawals'], 2) }}</td>
                <td class="money text-warning">AED {{ number_format($row['tips'], 2) }}</td>
                <td class="money {{ $row['net_cash'] >= 0 ? 'text-success' : 'text-danger' }}">AED {{ number_format($row['net_cash'], 2) }}</td>
                <td class="money text-success">AED {{ number_format($row['closing_cash'], 2) }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="11" class="text-center text-muted py-5">{{ __('ui.no_records_found') }}</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="d-md-none p-3">
        @forelse($dailyRows as $row)
          <div class="daily-card mb-3">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
              <div>
                <div class="fw-bold date-text">{{ $row['date'] }}</div>
                <small class="text-muted">{{ $row['transactions_count'] }} {{ __('ui.records') }}</small>
              </div>
              <div class="money text-success">AED {{ number_format($row['closing_cash'], 2) }}</div>
            </div>

            <div class="daily-line">
              <span>{{ __('ui.opening_cash_balance') }}</span>
              <span class="money">AED {{ number_format($row['opening_cash'], 2) }}</span>
            </div>
            <div class="daily-line">
              <span>{{ __('ui.total_revenues') }}</span>
              <span class="money text-success">AED {{ number_format($row['revenues'], 2) }}</span>
            </div>
            <div class="daily-line">
              <span>{{ __('ui.cash_revenues') }}</span>
              <span class="money text-success">AED {{ number_format($row['cash_revenues'], 2) }}</span>
            </div>
            <div class="daily-line">
              <span>{{ __('ui.visa_revenues') }}</span>
              <span class="money text-info">AED {{ number_format($row['visa_revenues'], 2) }}</span>
            </div>
            <div class="daily-line">
              <span>{{ __('ui.total_expenses') }}</span>
              <span class="money text-danger">AED {{ number_format($row['expenses'], 2) }}</span>
            </div>
            <div class="daily-line">
              <span>{{ __('ui.cash_withdrawals') }} / {{ __('ui.tips') }}</span>
              <span class="money text-warning">AED {{ number_format($row['cash_withdrawals'] + $row['tips'], 2) }}</span>
            </div>
            <div class="daily-line">
              <span>{{ __('ui.net_cash_movement') }}</span>
              <span class="money {{ $row['net_cash'] >= 0 ? 'text-success' : 'text-danger' }}">AED {{ number_format($row['net_cash'], 2) }}</span>
            </div>
          </div>
        @empty
          <div class="text-center text-muted py-4">{{ __('ui.no_records_found') }}</div>
        @endforelse
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header">
        <h5 class="mb-1">{{ __('ui.historical_treasury_snapshot') }}</h5>
        <small class="text-muted">{{ __('ui.historical_treasury_snapshot_hint') }}</small>
      </div>
      <div class="table-responsive d-none d-md-block">
        <table class="table table-hover report-table mb-0">
          <thead>
            <tr>
              <th>{{ __('ui.date') }}</th>
              <th>{{ __('ui.cutoff_time') }}</th>
              <th>{{ __('ui.total_revenues') }}</th>
              <th>{{ __('ui.total_expenses') }}</th>
              <th>{{ __('ui.net_cash_movement') }}</th>
              <th>{{ __('ui.cash_at_cutoff') }}</th>
              <th>{{ __('ui.current_cash_balance') }}</th>
              <th>{{ __('ui.difference_after_changes') }}</th>
              <th>{{ __('ui.after_cutoff_changes') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($historicalRows as $row)
              <tr>
                <td class="fw-semibold date-text">{{ $row['date'] }}</td>
                <td class="date-text">{{ $row['cutoff_at'] }}</td>
                <td class="money text-success">AED {{ number_format($row['revenues'], 2) }}</td>
                <td class="money text-danger">AED {{ number_format($row['expenses'], 2) }}</td>
                <td class="money {{ $row['cash_movement'] >= 0 ? 'text-success' : 'text-danger' }}">AED {{ number_format($row['cash_movement'], 2) }}</td>
                <td class="money text-info">AED {{ number_format($row['closing_cash'], 2) }}</td>
                <td class="money text-success">AED {{ number_format($row['current_closing_cash'], 2) }}</td>
                <td class="money {{ abs($row['cash_difference']) < 0.005 ? '' : ($row['cash_difference'] > 0 ? 'text-success' : 'text-danger') }}">
                  AED {{ number_format($row['cash_difference'], 2) }}
                </td>
                <td>
                  <span class="badge bg-label-{{ $row['late_changes'] > 0 ? 'warning' : 'secondary' }}">
                    {{ $row['late_changes'] }} {{ __('ui.records') }}
                  </span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center text-muted py-5">{{ __('ui.no_records_found') }}</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="d-md-none p-3">
        @forelse($historicalRows as $row)
          <div class="daily-card mb-3">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
              <div>
                <div class="fw-bold date-text">{{ $row['date'] }}</div>
                <small class="text-muted date-text">{{ $row['cutoff_at'] }}</small>
              </div>
              <span class="badge bg-label-{{ $row['late_changes'] > 0 ? 'warning' : 'secondary' }}">
                {{ $row['late_changes'] }} {{ __('ui.records') }}
              </span>
            </div>

            <div class="daily-line">
              <span>{{ __('ui.total_revenues') }}</span>
              <span class="money text-success">AED {{ number_format($row['revenues'], 2) }}</span>
            </div>
            <div class="daily-line">
              <span>{{ __('ui.total_expenses') }}</span>
              <span class="money text-danger">AED {{ number_format($row['expenses'], 2) }}</span>
            </div>
            <div class="daily-line">
              <span>{{ __('ui.cash_at_cutoff') }}</span>
              <span class="money text-info">AED {{ number_format($row['closing_cash'], 2) }}</span>
            </div>
            <div class="daily-line">
              <span>{{ __('ui.current_cash_balance') }}</span>
              <span class="money text-success">AED {{ number_format($row['current_closing_cash'], 2) }}</span>
            </div>
            <div class="daily-line">
              <span>{{ __('ui.difference_after_changes') }}</span>
              <span class="money {{ abs($row['cash_difference']) < 0.005 ? '' : ($row['cash_difference'] > 0 ? 'text-success' : 'text-danger') }}">
                AED {{ number_format($row['cash_difference'], 2) }}
              </span>
            </div>
          </div>
        @empty
          <div class="text-center text-muted py-4">{{ __('ui.no_records_found') }}</div>
        @endforelse
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex flex-wrap justify-content-between gap-2">
        <div>
          <h5 class="mb-1">{{ __('ui.treasury_changes_log') }}</h5>
          <small class="text-muted">{{ __('ui.treasury_changes_log_hint') }}</small>
        </div>
        <span class="badge bg-label-primary align-self-start">{{ $totals['audit_count'] }} {{ __('ui.records') }}</span>
      </div>
      <div class="card-body">
        @forelse($auditRows as $row)
          <details class="audit-card mb-3">
            <summary class="audit-summary">
              <div class="d-flex flex-wrap justify-content-between gap-3">
                <div class="d-flex flex-column gap-2">
                  <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="badge bg-label-{{ $row['action'] === 'deleted' ? 'danger' : ($row['action'] === 'updated' ? 'warning' : 'success') }}">
                      {{ __('ui.audit_action_'.$row['action']) }}
                    </span>
                    <span class="fw-bold">{{ $row['summary']['type'] }}</span>
                    <span class="money {{ $row['action'] === 'deleted' ? 'text-danger' : 'text-success' }}">{{ $row['summary']['amount'] }}</span>
                  </div>
                  @if($row['summary']['person'] || $row['summary']['description'])
                    <div class="text-muted small audit-value">
                      {{ $row['summary']['person'] ?: '---' }}
                      @if($row['summary']['description'])
                        <span class="mx-1">|</span>{{ $row['summary']['description'] }}
                      @endif
                    </div>
                  @endif

                  <div class="audit-meta">
                    <span class="audit-pill date-text">{{ $row['logged_at'] }}</span>
                    <span class="audit-pill">{{ $accountLabels[$row['account']] ?? ($row['account'] ?: '---') }}</span>
                    <span class="audit-pill date-text">{{ $row['summary']['date'] }}</span>
                    <span class="audit-pill">#{{ $row['model_id'] }}</span>
                  </div>
                </div>

                <div class="text-muted small text-md-end">
                  <div>{{ $row['actor_name'] ?: $row['actor_username'] }}</div>
                  <div dir="ltr">{{ $row['ip'] }}</div>
                </div>
              </div>
            </summary>

            <div class="p-3 border-top">
              <div class="audit-cash-grid mb-3">
                <div class="audit-detail-item">
                  <div class="small text-muted">{{ __('ui.cash_balance_before_operation') }}</div>
                  <div class="money fw-bold">AED {{ number_format($row['cash_before'], 2) }}</div>
                </div>
                <div class="audit-detail-item">
                  <div class="small text-muted">{{ __('ui.cash_balance_after_operation') }}</div>
                  <div class="money fw-bold text-success">AED {{ number_format($row['cash_after'], 2) }}</div>
                </div>
                <div class="audit-detail-item">
                  <div class="small text-muted">{{ __('ui.cash_balance_operation_change') }}</div>
                  <div class="money fw-bold {{ $row['cash_change'] >= 0 ? 'text-success' : 'text-danger' }}">
                    AED {{ number_format($row['cash_change'], 2) }}
                  </div>
                </div>
              </div>

              <div class="audit-detail-grid mb-3">
                @foreach($row['snapshot'] as $item)
                  <div class="audit-detail-item">
                    <div class="small text-muted">{{ $item['label'] }}</div>
                    <div class="fw-semibold audit-value {{ in_array($item['field'], ['amount', 'unit_price'], true) ? 'money text-success' : '' }}">
                      {{ $item['value'] }}
                    </div>
                  </div>
                @endforeach
              </div>

              <div class="row g-2 mb-3">
                <div class="col-md-4">
                  <div class="small text-muted">{{ __('ui.user') }}</div>
                  <div class="fw-semibold">{{ $row['actor_name'] ?: $row['actor_username'] }}</div>
                </div>
                <div class="col-md-8">
                  <div class="small text-muted">{{ __('ui.url') }}</div>
                  <div class="small audit-value" dir="ltr">{{ $row['url'] ?: '---' }}</div>
                </div>
              </div>

              @if($row['action'] === 'updated')
                <div class="small text-muted mb-2">{{ __('ui.before_update') }} / {{ __('ui.after_update') }}</div>
                <div class="audit-change-grid">
                  @forelse($row['changes'] as $change)
                    <div class="audit-change-item">
                      <div class="fw-semibold mb-2">{{ $change['label'] }}</div>
                      <div class="d-flex justify-content-between gap-2 mb-1">
                        <span class="small text-muted">{{ __('ui.before_update') }}</span>
                        <span class="audit-value text-danger">{{ $change['old'] }}</span>
                      </div>
                      <div class="d-flex justify-content-between gap-2">
                        <span class="small text-muted">{{ __('ui.after_update') }}</span>
                        <span class="audit-value text-success">{{ $change['new'] }}</span>
                      </div>
                    </div>
                  @empty
                    <div class="text-muted">{{ __('ui.no_records_found') }}</div>
                  @endforelse
                </div>
              @else
                <div class="small text-muted">{{ __('ui.transaction_details') }}</div>
              @endif
            </div>
          </details>
        @empty
          <div class="text-center text-muted py-5">{{ __('ui.no_treasury_changes_found') }}</div>
        @endforelse
      </div>
    </div>
  </div>
</div>
