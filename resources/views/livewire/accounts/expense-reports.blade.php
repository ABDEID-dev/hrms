<div dir="rtl">
  @section('title', 'تقارير المصاريف')

  @section('page-style')
    <style>
      .expense-reports .summary-box {
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .5rem;
        padding: .9rem 1rem;
        background: rgba(var(--bs-body-bg-rgb), .45);
        min-height: 100%;
      }

      .expense-reports .summary-value {
        direction: ltr;
        font-weight: 700;
        font-size: 1.2rem;
      }

      .expense-reports .amount-cell,
      .expense-reports .date-cell {
        direction: ltr;
        text-align: center;
        white-space: nowrap;
      }

      .expense-reports .report-table th {
        white-space: nowrap;
        vertical-align: middle;
      }

      .expense-reports .detail-text {
        min-width: 180px;
        white-space: normal;
      }

      .expense-reports .note-cell {
        min-width: 220px;
        white-space: normal;
      }

      @media (max-width: 767.98px) {
        .expense-reports .card-header {
          align-items: flex-start !important;
          flex-direction: column;
          gap: .75rem;
        }

        .expense-reports .table-responsive {
          max-height: none;
        }
      }
    </style>
  @endsection

  <div class="expense-reports">
    <div class="card mb-4">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-0">تقارير المصاريف</h5>
          <small class="text-muted">تحليل المشتريات والسحوبات والتيبس لكل الفروع حسب الفترة.</small>
        </div>
        <span class="badge bg-label-primary">{{ number_format($totals['count']) }} حركة</span>
      </div>
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-lg-2 col-md-4">
            <label class="form-label">الفرع</label>
            <select wire:model.live="account" class="form-select">
              <option value="all">كل الفروع</option>
              @foreach($accountOptions as $key => $name)
                <option value="{{ $key }}">{{ $name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-lg-2 col-md-4">
            <label class="form-label">نوع المصروف</label>
            <select wire:model.live="expenseKind" class="form-select">
              <option value="all">كل الأنواع</option>
              <option value="purchase">المشتريات</option>
              <option value="cash_withdrawal">السحوبات</option>
              <option value="tip">التيبس</option>
              <option value="advance">سلف الموظفين</option>
            </select>
          </div>
          <div class="col-lg-2 col-md-4">
            <label class="form-label">من</label>
            <input wire:model.live="fromDate" type="date" class="form-control date-cell">
          </div>
          <div class="col-lg-2 col-md-4">
            <label class="form-label">إلى</label>
            <input wire:model.live="toDate" type="date" class="form-control date-cell">
          </div>
          <div class="col-lg-3 col-md-4">
            <label class="form-label">بحث</label>
            <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="اسم مشتريات، شخص، ملاحظة...">
          </div>
          <div class="col-lg-1 col-md-4 d-grid gap-2">
            <button wire:click="setThisMonth" type="button" class="btn btn-label-primary">الشهر</button>
            <button wire:click="resetFilters" type="button" class="btn btn-label-secondary">مسح</button>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-xl-3 col-md-6">
        <div class="summary-box">
          <div class="text-muted small mb-1">إجمالي المصاريف</div>
          <div class="summary-value text-danger">AED {{ number_format($totals['total'], 2) }}</div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="summary-box">
          <div class="text-muted small mb-1">المشتريات</div>
          <div class="summary-value">AED {{ number_format($totals['purchase'], 2) }}</div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="summary-box">
          <div class="text-muted small mb-1">السحوبات</div>
          <div class="summary-value text-warning">AED {{ number_format($totals['cash_withdrawal'], 2) }}</div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="summary-box">
          <div class="text-muted small mb-1">التيبس</div>
          <div class="summary-value text-info">AED {{ number_format($totals['tip'], 2) }}</div>
        </div>
      </div>
      <div class="col-xl-3 col-md-6">
        <div class="summary-box">
          <div class="text-muted small mb-1">سلف الموظفين</div>
          <div class="summary-value text-success">AED {{ number_format($totals['advance'], 2) }}</div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-lg-7">
        <div class="card h-100">
          <div class="card-header border-bottom">
            <h5 class="mb-0">توزيع المصاريف حسب الفرع</h5>
          </div>
          <div class="table-responsive">
            <table class="table report-table align-middle mb-0">
              <thead>
                <tr>
                  <th>الفرع</th>
                  <th class="text-center">المشتريات</th>
                  <th class="text-center">السحوبات</th>
                  <th class="text-center">التيبس</th>
                  <th class="text-center">سلف الموظفين</th>
                  <th class="text-center">الإجمالي</th>
                  <th class="text-center">عدد</th>
                </tr>
              </thead>
              <tbody>
                @forelse($accountRows as $row)
                  <tr>
                    <td>{{ $row['name'] }}</td>
                    <td class="amount-cell">AED {{ number_format($row['purchase'], 2) }}</td>
                    <td class="amount-cell text-warning">AED {{ number_format($row['cash_withdrawal'], 2) }}</td>
                    <td class="amount-cell text-info">AED {{ number_format($row['tip'], 2) }}</td>
                    <td class="amount-cell text-success">AED {{ number_format($row['advance'], 2) }}</td>
                    <td class="amount-cell text-danger fw-semibold">AED {{ number_format($row['total'], 2) }}</td>
                    <td class="amount-cell">{{ number_format($row['count']) }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center text-muted py-4">لا توجد مصاريف ضمن الفلاتر الحالية.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="card h-100">
          <div class="card-header border-bottom">
            <h5 class="mb-0">توزيع المصاريف حسب النوع</h5>
          </div>
          <div class="table-responsive">
            <table class="table report-table align-middle mb-0">
              <thead>
                <tr>
                  <th>النوع</th>
                  <th class="text-center">الإجمالي</th>
                  <th class="text-center">عدد الحركات</th>
                </tr>
              </thead>
              <tbody>
                @foreach($kindRows as $row)
                  <tr>
                    <td>{{ $row['label'] }}</td>
                    <td class="amount-cell">AED {{ number_format($row['total'], 2) }}</td>
                    <td class="amount-cell">{{ number_format($row['count']) }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-0">حركة المصاريف التفصيلية</h5>
          <small class="text-muted">كل عملية بتاريخها والفرع ونوع المصروف والتفاصيل.</small>
        </div>
        <span class="badge bg-label-secondary">{{ number_format($transactions->total()) }} نتيجة</span>
      </div>
      <div class="table-responsive">
        <table class="table report-table align-middle mb-0">
          <thead>
            <tr>
              <th class="text-center">التاريخ</th>
              <th class="text-center">الوقت</th>
              <th>الفرع</th>
              <th>النوع</th>
              <th>التفاصيل</th>
              <th class="text-center">الكمية</th>
              <th class="text-center">السعر</th>
              <th class="text-center">الإجمالي</th>
              <th>ملاحظة</th>
            </tr>
          </thead>
          <tbody>
            @forelse($transactions as $transaction)
              @php($kind = $transaction->expense_kind ?: 'purchase')
              <tr>
                <td class="date-cell">{{ \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') }}</td>
                <td class="date-cell">{{ $transaction->created_at?->timezone('Asia/Dubai')->format('H:i') }}</td>
                <td>{{ $this->accountLabel($transaction->account) }}</td>
                <td>
                  <span class="badge bg-label-{{ $kind === 'purchase' ? 'primary' : ($kind === 'tip' ? 'info' : ($kind === 'advance' ? 'success' : 'warning')) }}">
                    {{ $this->expenseKindLabel($kind) }}
                  </span>
                </td>
                <td class="detail-text">{{ $this->expenseTitle($transaction) }}</td>
                <td class="amount-cell">{{ $kind === 'purchase' ? number_format((float) ($transaction->quantity ?: 1), 0) : '---' }}</td>
                <td class="amount-cell">{{ $transaction->unit_price ? 'AED '.number_format((float) $transaction->unit_price, 2) : '---' }}</td>
                <td class="amount-cell text-danger fw-semibold">AED {{ number_format((float) $transaction->amount, 2) }}</td>
                <td class="note-cell">{{ $transaction->note ?: '---' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center text-muted py-5">لا توجد حركات مصاريف مطابقة للفلاتر.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @if($transactions->hasPages())
        <div class="card-body border-top">
          {{ $transactions->links() }}
        </div>
      @endif
    </div>
  </div>
</div>
