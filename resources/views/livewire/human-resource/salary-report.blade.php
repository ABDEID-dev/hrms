<div dir="rtl">
  @section('title', __('ui.salary_report'))

  @section('page-style')
    <style>
      .salary-report-page .summary-card {
        border: 1px solid rgba(var(--bs-border-color-rgb), .42);
        border-radius: .65rem;
        background: rgba(var(--bs-body-bg-rgb), .45);
        padding: 1rem;
        min-height: 92px;
      }

      .salary-report-page .summary-label {
        color: var(--bs-secondary-color);
        font-size: .85rem;
      }

      .salary-report-page .summary-value,
      .salary-report-page .money {
        direction: ltr;
        font-weight: 800;
        white-space: nowrap;
      }

      .salary-report-page .summary-value {
        font-size: 1.18rem;
        margin-top: .35rem;
      }

      .salary-report-page .report-table th {
        background: #21445b;
        color: #fff;
        white-space: nowrap;
        vertical-align: middle;
      }

      .salary-report-page .report-table td {
        vertical-align: middle;
      }

      .salary-report-page .employee-card {
        border: 1px solid rgba(var(--bs-border-color-rgb), .42);
        border-radius: .75rem;
        background: rgba(var(--bs-body-bg-rgb), .35);
        padding: 1rem;
      }

      .salary-report-page .mini-line {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        padding: .45rem 0;
        border-bottom: 1px solid rgba(var(--bs-border-color-rgb), .28);
      }

      .salary-report-page .mini-line:last-child {
        border-bottom: 0;
      }

      .salary-report-page .withdrawal-details summary {
        cursor: pointer;
        list-style: none;
      }

      .salary-report-page .withdrawal-details summary::-webkit-details-marker {
        display: none;
      }

      .salary-report-page .withdrawal-details-panel {
        border: 1px solid rgba(var(--bs-border-color-rgb), .35);
        border-radius: .5rem;
        background: rgba(var(--bs-body-bg-rgb), .55);
        margin-top: .6rem;
        padding: .65rem;
        min-width: 270px;
      }

      .salary-report-page .withdrawal-record {
        display: grid;
        grid-template-columns: 82px 1fr auto;
        gap: .65rem;
        align-items: start;
        padding: .45rem 0;
        border-bottom: 1px solid rgba(var(--bs-border-color-rgb), .25);
      }

      .salary-report-page .withdrawal-record:last-child {
        border-bottom: 0;
      }

      @media (max-width: 767.98px) {
        .salary-report-page {
          margin-inline: -.75rem;
        }

        .salary-report-page .card {
          border-radius: 0;
        }

        .salary-report-page .summary-card {
          min-height: auto;
          padding: .85rem;
        }

        .salary-report-page .withdrawal-record {
          grid-template-columns: 1fr;
          gap: .2rem;
        }
      }
    </style>
  @endsection

  <div class="salary-report-page">
    <div class="card mb-4">
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
          <h5 class="mb-1">{{ __('ui.salary_report') }}</h5>
          <small class="text-muted">{{ __('ui.salary_report_hint') }} {{ __('ui.salary_report_maktoom_cash_note') }}</small>
        </div>

        <div class="d-flex flex-wrap gap-2">
          <input wire:model.defer="selectedMonth" type="month" class="form-control" style="min-width: 170px;">
          <button wire:click="applyMonth" type="button" class="btn btn-primary">
            <i class="ti ti-calendar-stats me-1"></i>{{ __('ui.show_report') }}
          </button>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="summary-card">
          <div class="summary-label">{{ __('ui.total_employees') }}</div>
          <div class="summary-value">{{ $summary['employees_count'] }}</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="summary-card">
          <div class="summary-label">{{ __('ui.total_gross_salaries') }}</div>
          <div class="summary-value text-info">AED {{ number_format($summary['gross_salaries'], 2) }}</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="summary-card">
          <div class="summary-label">{{ __('ui.total_salary_withdrawals') }}</div>
          <div class="summary-value text-warning">AED {{ number_format($summary['withdrawals'], 2) }}</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="summary-card">
          <div class="summary-label">{{ __('ui.total_net_salaries') }}</div>
          <div class="summary-value text-success">AED {{ number_format($summary['net_salaries'], 2) }}</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="summary-card">
          <div class="summary-label">{{ __('ui.cash_treasury_available') }} - {{ __('ui.inventory_account_maktoom') }}</div>
          <div class="summary-value text-success">AED {{ number_format($summary['cash_treasury'], 2) }}</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="summary-card">
          <div class="summary-label">{{ __('ui.cash_after_salary_reserve') }}</div>
          <div class="summary-value {{ $summary['cash_after_payroll'] >= 0 ? 'text-success' : 'text-danger' }}">
            AED {{ number_format($summary['cash_after_payroll'], 2) }}
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="summary-card">
          <div class="summary-label">{{ __('ui.total_attendance_days') }}</div>
          <div class="summary-value">{{ $summary['attendance_days'] }}</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="summary-card">
          <div class="summary-label">{{ __('ui.attendance_salary_value') }}</div>
          <div class="summary-value text-info">AED {{ number_format($summary['attendance_value'], 2) }}</div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
          <h5 class="mb-1">{{ __('ui.employee_salary_details') }}</h5>
          <small class="text-muted">{{ $fromDate }} - {{ $toDate }}</small>
        </div>
        <input wire:model.live.debounce.300ms="search" type="text" class="form-control" style="max-width: 280px;" placeholder="{{ __('ui.search_employees') }}">
      </div>

      <div class="card-body p-0">
        <div class="table-responsive d-none d-md-block">
          <table class="table table-hover report-table mb-0">
            <thead>
              <tr>
                <th>{{ __('ui.employee_name') }}</th>
                <th>{{ __('ui.position') }}</th>
                <th>{{ __('ui.gross_salary') }}</th>
                <th>{{ __('ui.daily_salary') }}</th>
                <th>{{ __('ui.attendance_days') }}</th>
                <th>{{ __('ui.attendance_salary_value') }}</th>
                <th>{{ __('ui.withdrawals') }}</th>
                <th>{{ __('ui.remaining_salary') }}</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($rows as $row)
                <tr>
                  <td class="fw-semibold">{{ $row['name'] }}</td>
                  <td>{{ $row['position'] ?: '---' }}</td>
                  <td class="money text-info">AED {{ number_format($row['gross_salary'], 2) }}</td>
                  <td class="money">AED {{ number_format($row['daily_salary'], 2) }}</td>
                  <td>{{ $row['attendance_days'] }}</td>
                  <td class="money text-info">AED {{ number_format($row['attendance_value'], 2) }}</td>
                  <td>
                    @if($row['withdrawals_count'] > 0)
                      <details class="withdrawal-details">
                        <summary>
                          <div class="money text-warning d-inline-flex align-items-center gap-1">
                            AED {{ number_format($row['withdrawals'], 2) }}
                            <i class="ti ti-chevron-down"></i>
                          </div>
                          <div><small class="text-muted">{{ $row['withdrawals_count'] }} {{ __('ui.records') }}</small></div>
                        </summary>
                        <div class="withdrawal-details-panel">
                          @foreach($row['discount_details'] as $discount)
                            <div class="withdrawal-record">
                              <div class="small text-muted">{{ $discount['date'] }}</div>
                              <div>
                                <div class="fw-semibold">{{ $discount['reason'] }}</div>
                                <small class="text-muted">{{ $discount['is_auto'] ? __('ui.auto_discount') : __('ui.manual_discount') }}</small>
                              </div>
                              <div class="money text-warning">AED {{ number_format($discount['amount'], 2) }}</div>
                            </div>
                          @endforeach
                        </div>
                      </details>
                    @else
                      <div class="money text-warning">AED {{ number_format($row['withdrawals'], 2) }}</div>
                      <small class="text-muted">0 {{ __('ui.records') }}</small>
                    @endif
                  </td>
                  <td class="money text-success">AED {{ number_format($row['remaining_salary'], 2) }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center text-muted py-5">{{ __('ui.no_employees_found') }}</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="d-md-none p-3">
          @forelse ($rows as $row)
            <div class="employee-card mb-3">
              <div class="d-flex justify-content-between gap-3 mb-2">
                <div>
                  <div class="fw-bold">{{ $row['name'] }}</div>
                  <small class="text-muted">{{ $row['position'] ?: '---' }}</small>
                </div>
                <div class="money text-success">AED {{ number_format($row['remaining_salary'], 2) }}</div>
              </div>

              <div class="mini-line">
                <span>{{ __('ui.gross_salary') }}</span>
                <span class="money">AED {{ number_format($row['gross_salary'], 2) }}</span>
              </div>
              <div class="mini-line">
                <span>{{ __('ui.daily_salary') }}</span>
                <span class="money">AED {{ number_format($row['daily_salary'], 2) }}</span>
              </div>
              <div class="mini-line">
                <span>{{ __('ui.attendance_days') }}</span>
                <span>{{ $row['attendance_days'] }}</span>
              </div>
              <div class="mini-line">
                <span>{{ __('ui.attendance_salary_value') }}</span>
                <span class="money">AED {{ number_format($row['attendance_value'], 2) }}</span>
              </div>
              <div class="mini-line">
                <span>{{ __('ui.withdrawals') }}</span>
                <span class="money text-warning">AED {{ number_format($row['withdrawals'], 2) }}</span>
              </div>
              @if($row['withdrawals_count'] > 0)
                <details class="withdrawal-details mt-2">
                  <summary class="btn btn-outline-warning btn-sm w-100">
                    <i class="ti ti-list-details me-1"></i>{{ __('ui.show_withdrawal_details') }}
                  </summary>
                  <div class="withdrawal-details-panel">
                    @foreach($row['discount_details'] as $discount)
                      <div class="withdrawal-record">
                        <div class="small text-muted">{{ $discount['date'] }}</div>
                        <div>
                          <div class="fw-semibold">{{ $discount['reason'] }}</div>
                          <small class="text-muted">{{ $discount['is_auto'] ? __('ui.auto_discount') : __('ui.manual_discount') }}</small>
                        </div>
                        <div class="money text-warning">AED {{ number_format($discount['amount'], 2) }}</div>
                      </div>
                    @endforeach
                  </div>
                </details>
              @endif
            </div>
          @empty
            <div class="text-center text-muted py-4">{{ __('ui.no_employees_found') }}</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
</div>
