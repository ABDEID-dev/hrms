<div dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
  @section('title', __('accounts.treasury').' '.$accountName)

  @section('page-style')
    <style>
      .treasury-page .amount-cell {
        direction: ltr;
        text-align: center;
        white-space: nowrap;
      }

      .treasury-page input[type="date"],
      .treasury-page .date-text {
        direction: ltr;
        unicode-bidi: isolate;
        text-align: center;
      }

      .treasury-page .date-text {
        display: inline-block;
      }

      .treasury-page .summary-card {
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .5rem;
        padding: 1rem;
        background: rgba(var(--bs-body-bg-rgb), .45);
        height: 100%;
      }

      .treasury-page .summary-value {
        direction: ltr;
        font-weight: 700;
        font-size: 1.25rem;
      }

      .treasury-page .sheet-table th {
        background: #2f3349;
        color: #fff;
        white-space: nowrap;
      }

      .treasury-page .mobile-label {
        display: none;
      }

      .treasury-page .mobile-daily-list,
      .treasury-page .mobile-transaction-list {
        display: none;
      }

      @media (max-width: 767.98px) {
        .treasury-page {
          margin-inline: -.75rem;
        }

        .treasury-page .card {
          border-radius: 0;
        }

        .treasury-page .card-header {
          align-items: stretch !important;
        }

        .treasury-page .card-header > div,
        .treasury-page .card-header > button {
          width: 100%;
        }

        .treasury-page .summary-card {
          padding: .85rem;
        }

        .treasury-page .summary-value {
          font-size: 1.05rem;
        }

        .treasury-page .daily-table-wrap,
        .treasury-page .transactions-table-wrap {
          display: none;
        }

        .treasury-page .mobile-daily-list,
        .treasury-page .mobile-transaction-list {
          display: grid;
          gap: .75rem;
          padding: .9rem;
          border-top: 1px solid rgba(var(--bs-border-color-rgb), .35);
        }

        .treasury-page .mobile-day-card,
        .treasury-page .mobile-transaction-card {
          border: 1px solid rgba(var(--bs-border-color-rgb), .45);
          border-radius: .65rem;
          background: rgba(var(--bs-body-bg-rgb), .45);
          overflow: hidden;
        }

        .treasury-page .mobile-day-header,
        .treasury-page .mobile-transaction-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          gap: .75rem;
          padding: .75rem .85rem;
          background: rgba(115, 103, 240, .08);
          border-bottom: 1px solid rgba(var(--bs-border-color-rgb), .35);
          font-weight: 700;
        }

        .treasury-page .mobile-day-grid,
        .treasury-page .mobile-transaction-grid {
          display: grid;
          grid-template-columns: repeat(2, minmax(0, 1fr));
          gap: 0;
        }

        .treasury-page .mobile-day-item,
        .treasury-page .mobile-transaction-item {
          padding: .75rem .85rem;
          border-bottom: 1px solid rgba(var(--bs-border-color-rgb), .28);
        }

        .treasury-page .mobile-day-item:nth-child(odd),
        .treasury-page .mobile-transaction-item:nth-child(odd) {
          border-inline-end: 1px solid rgba(var(--bs-border-color-rgb), .28);
        }

        .treasury-page .mobile-day-label,
        .treasury-page .mobile-transaction-label {
          color: var(--bs-secondary-color);
          font-size: .75rem;
          margin-bottom: .3rem;
        }

        .treasury-page .mobile-day-value,
        .treasury-page .mobile-transaction-value {
          direction: ltr;
          font-weight: 700;
          white-space: nowrap;
        }

        .treasury-page .mobile-transaction-note {
          direction: auto;
          unicode-bidi: plaintext;
          white-space: pre-wrap;
          word-break: break-word;
          line-height: 1.6;
        }
      }

      @media (max-width: 575.98px) {
        .treasury-page .summary-card {
          text-align: center;
        }

        .treasury-page .summary-value {
          font-size: 1rem;
        }

        .treasury-page .mobile-day-grid,
        .treasury-page .mobile-transaction-grid {
          grid-template-columns: 1fr;
        }

        .treasury-page .mobile-day-item:nth-child(odd),
        .treasury-page .mobile-transaction-item:nth-child(odd) {
          border-inline-end: 0;
        }
      }
    </style>
  @endsection

  <div class="treasury-page">
    <div class="card mb-4">
      <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
          <h5 class="mb-0">{{ __('accounts.treasury') }} {{ $accountName }}</h5>
          <small class="text-muted">{{ __('accounts.treasury_hint') }}</small>
        </div>
        <button wire:click="resetToThisMonth" type="button" class="btn btn-sm btn-label-primary">
          {{ __('accounts.reset_this_month') }}
        </button>
      </div>

      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label">{{ __('accounts.from_date') }}</label>
            <input wire:model.live="fromDate" type="date" dir="ltr" lang="en" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('accounts.to_date') }}</label>
            <input wire:model.live="toDate" type="date" dir="ltr" lang="en" class="form-control">
          </div>
        </div>
      </div>
    </div>

    <div class="row g-2 g-md-3 mb-4">
      <div class="col-xl-3 col-md-6 col-6">
        <div class="summary-card">
          <div class="text-muted small mb-1">{{ __('accounts.opening_balance') }}</div>
          <div class="summary-value">AED {{ number_format($summary['opening_balance'], 2) }}</div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6 col-6">
        <div class="summary-card">
          <div class="text-muted small mb-1">{{ __('accounts.revenues') }}</div>
          <div class="summary-value text-success">AED {{ number_format($summary['revenues'], 2) }}</div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6 col-6">
        <div class="summary-card">
          <div class="text-muted small mb-1">{{ __('accounts.cash_revenues') }}</div>
          <div class="summary-value text-success">AED {{ number_format($summary['cash_revenues'], 2) }}</div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6 col-6">
        <div class="summary-card">
          <div class="text-muted small mb-1">{{ __('accounts.visa_revenues') }}</div>
          <div class="summary-value text-info">AED {{ number_format($summary['visa_revenues'], 2) }}</div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6 col-6">
        <div class="summary-card">
          <div class="text-muted small mb-1">{{ __('accounts.purchases') }}</div>
          <div class="summary-value text-danger">AED {{ number_format($summary['purchases'], 2) }}</div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6 col-6">
        <div class="summary-card">
          <div class="text-muted small mb-1">{{ __('accounts.cash_withdrawals') }}</div>
          <div class="summary-value text-warning">AED {{ number_format($summary['cash_withdrawals'], 2) }}</div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6 col-6">
        <div class="summary-card">
          <div class="text-muted small mb-1">{{ __('accounts.tips') }}</div>
          <div class="summary-value text-warning">AED {{ number_format($summary['tips'], 2) }}</div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6 col-6">
        <div class="summary-card">
          <div class="text-muted small mb-1">المرتبات</div>
          <div class="summary-value text-danger">AED {{ number_format($summary['salaries'], 2) }}</div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6 col-6">
        <div class="summary-card">
          <div class="text-muted small mb-1">{{ __('accounts.total_expenses') }}</div>
          <div class="summary-value text-danger">AED {{ number_format($summary['expenses'], 2) }}</div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6 col-6">
        <div class="summary-card">
          <div class="text-muted small mb-1">{{ __('accounts.net_movement') }}</div>
          <div class="summary-value {{ $summary['net_movement'] >= 0 ? 'text-success' : 'text-danger' }}">
            AED {{ number_format($summary['net_movement'], 2) }}
          </div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6 col-12">
        <div class="summary-card">
          <div class="text-muted small mb-1">{{ __('accounts.closing_balance') }}</div>
          <div class="summary-value {{ $summary['closing_balance'] >= 0 ? 'text-success' : 'text-danger' }}">
            AED {{ number_format($summary['closing_balance'], 2) }}
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h5 class="mb-0">{{ __('accounts.daily_report') }}</h5>
      </div>
      <div class="table-responsive daily-table-wrap">
        <table class="table sheet-table align-middle mb-0">
          <thead>
            <tr>
              <th class="text-center">{{ __('accounts.date') }}</th>
              <th class="text-center">{{ __('accounts.revenues') }}</th>
              <th class="text-center">{{ __('accounts.cash_revenues') }}</th>
              <th class="text-center">{{ __('accounts.visa_revenues') }}</th>
              <th class="text-center">{{ __('accounts.purchases') }}</th>
              <th class="text-center">{{ __('accounts.cash_withdrawals') }}</th>
              <th class="text-center">{{ __('accounts.tips') }}</th>
              <th class="text-center">المرتبات</th>
              <th class="text-center">{{ __('accounts.total_expenses') }}</th>
              <th class="text-center">{{ __('accounts.net_movement') }}</th>
              <th class="text-center">{{ __('accounts.treasury_balance') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($dailyReports as $day)
              <tr>
                <td class="date-text">{{ $day['date'] }}</td>
                <td class="amount-cell text-success">AED {{ number_format($day['revenues'], 2) }}</td>
                <td class="amount-cell text-success">AED {{ number_format($day['cash_revenues'], 2) }}</td>
                <td class="amount-cell text-info">AED {{ number_format($day['visa_revenues'], 2) }}</td>
                <td class="amount-cell text-danger">AED {{ number_format($day['purchases'], 2) }}</td>
                <td class="amount-cell text-warning">AED {{ number_format($day['cash_withdrawals'], 2) }}</td>
                <td class="amount-cell text-warning">AED {{ number_format($day['tips'], 2) }}</td>
                <td class="amount-cell text-danger">AED {{ number_format($day['salaries'], 2) }}</td>
                <td class="amount-cell text-danger">AED {{ number_format($day['expenses'], 2) }}</td>
                <td class="amount-cell {{ $day['net'] >= 0 ? 'text-success' : 'text-danger' }}">AED {{ number_format($day['net'], 2) }}</td>
                <td class="amount-cell fw-bold">AED {{ number_format($day['balance'], 2) }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="11" class="text-center text-muted py-5">{{ __('No data found') }}</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mobile-daily-list">
        @forelse($dailyReports as $day)
          <div class="mobile-day-card">
            <div class="mobile-day-header">
              <span>{{ __('accounts.date') }}</span>
              <span class="date-text">{{ $day['date'] }}</span>
            </div>
            <div class="mobile-day-grid">
              <div class="mobile-day-item">
                <div class="mobile-day-label">{{ __('accounts.revenues') }}</div>
                <div class="mobile-day-value text-success">AED {{ number_format($day['revenues'], 2) }}</div>
              </div>
              <div class="mobile-day-item">
                <div class="mobile-day-label">{{ __('accounts.cash_revenues') }}</div>
                <div class="mobile-day-value text-success">AED {{ number_format($day['cash_revenues'], 2) }}</div>
              </div>
              <div class="mobile-day-item">
                <div class="mobile-day-label">{{ __('accounts.visa_revenues') }}</div>
                <div class="mobile-day-value text-info">AED {{ number_format($day['visa_revenues'], 2) }}</div>
              </div>
              <div class="mobile-day-item">
                <div class="mobile-day-label">{{ __('accounts.purchases') }}</div>
                <div class="mobile-day-value text-danger">AED {{ number_format($day['purchases'], 2) }}</div>
              </div>
              <div class="mobile-day-item">
                <div class="mobile-day-label">{{ __('accounts.cash_withdrawals') }}</div>
                <div class="mobile-day-value text-warning">AED {{ number_format($day['cash_withdrawals'], 2) }}</div>
              </div>
              <div class="mobile-day-item">
                <div class="mobile-day-label">{{ __('accounts.tips') }}</div>
                <div class="mobile-day-value text-warning">AED {{ number_format($day['tips'], 2) }}</div>
              </div>
              <div class="mobile-day-item">
                <div class="mobile-day-label">المرتبات</div>
                <div class="mobile-day-value text-danger">AED {{ number_format($day['salaries'], 2) }}</div>
              </div>
              <div class="mobile-day-item">
                <div class="mobile-day-label">{{ __('accounts.total_expenses') }}</div>
                <div class="mobile-day-value text-danger">AED {{ number_format($day['expenses'], 2) }}</div>
              </div>
              <div class="mobile-day-item">
                <div class="mobile-day-label">{{ __('accounts.net_movement') }}</div>
                <div class="mobile-day-value {{ $day['net'] >= 0 ? 'text-success' : 'text-danger' }}">AED {{ number_format($day['net'], 2) }}</div>
              </div>
              <div class="mobile-day-item">
                <div class="mobile-day-label">{{ __('accounts.treasury_balance') }}</div>
                <div class="mobile-day-value">AED {{ number_format($day['balance'], 2) }}</div>
              </div>
            </div>
          </div>
        @empty
          <div class="text-center text-muted py-4">{{ __('No data found') }}</div>
        @endforelse
      </div>
    </div>

    <div class="card">
      <div class="card-header border-bottom">
        <h5 class="mb-0">{{ __('accounts.all_transactions') }}</h5>
      </div>
      <div class="table-responsive transactions-table-wrap">
        <table class="table align-middle mb-0">
          <thead>
            <tr>
              <th class="text-center">{{ __('accounts.date') }}</th>
              <th class="text-center">{{ __('accounts.time') }}</th>
              <th class="text-center">{{ __('accounts.expense_kind') }}</th>
              <th class="text-center">{{ __('accounts.note') }}</th>
              <th class="text-center">{{ __('accounts.total') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse($transactions as $transaction)
              @php
                $kind = $transaction->type === 'revenue'
                  ? __('accounts.revenues')
                  : match ($transaction->expense_kind ?: 'purchase') {
                    'cash_withdrawal' => __('accounts.cash_withdrawals'),
                    'tip' => __('accounts.tips'),
                    'salary' => 'المرتبات',
                    default => __('accounts.purchases'),
                  };
              @endphp
              <tr>
                <td class="date-text">{{ $transaction->date }}</td>
                <td class="text-center">{{ $transaction->created_at?->timezone('Asia/Dubai')->format('h:i A') }}</td>
                <td class="text-center">{{ $kind }}</td>
                <td>{{ $transaction->note ?: $transaction->service ?: $transaction->withdrawn_to ?: '---' }}</td>
                <td class="amount-cell {{ $transaction->type === 'revenue' ? 'text-success' : 'text-danger' }}">
                  AED {{ number_format((float) $transaction->amount, 2) }}
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-5">{{ __('No data found') }}</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mobile-transaction-list">
        @forelse($transactions as $transaction)
          @php
            $kind = $transaction->type === 'revenue'
              ? __('accounts.revenues')
              : match ($transaction->expense_kind ?: 'purchase') {
                'cash_withdrawal' => __('accounts.cash_withdrawals'),
                'tip' => __('accounts.tips'),
                'salary' => 'المرتبات',
                default => __('accounts.purchases'),
              };
          @endphp
          <div class="mobile-transaction-card">
            <div class="mobile-transaction-header">
              <span>{{ $kind }}</span>
              <span class="{{ $transaction->type === 'revenue' ? 'text-success' : 'text-danger' }}" dir="ltr">
                AED {{ number_format((float) $transaction->amount, 2) }}
              </span>
            </div>
            <div class="mobile-transaction-grid">
              <div class="mobile-transaction-item">
                <div class="mobile-transaction-label">{{ __('accounts.date') }}</div>
                <div class="mobile-transaction-value date-text">{{ $transaction->date }}</div>
              </div>
              <div class="mobile-transaction-item">
                <div class="mobile-transaction-label">{{ __('accounts.time') }}</div>
                <div class="mobile-transaction-value">{{ $transaction->created_at?->timezone('Asia/Dubai')->format('h:i A') }}</div>
              </div>
              <div class="mobile-transaction-item">
                <div class="mobile-transaction-label">{{ __('accounts.note') }}</div>
                <div class="mobile-transaction-note">{{ $transaction->note ?: $transaction->service ?: $transaction->withdrawn_to ?: '---' }}</div>
              </div>
            </div>
          </div>
        @empty
          <div class="text-center text-muted py-4">{{ __('No data found') }}</div>
        @endforelse
      </div>
    </div>
  </div>
</div>
