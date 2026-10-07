<div dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
  @section('title', 'إيرادات الموظفين')

  @section('page-style')
    <style>
      .employee-revenues-page .report-table th {
        background: #21445b;
        color: #fff;
        vertical-align: middle;
        white-space: nowrap;
      }

      .employee-revenues-page .amount-cell,
      .employee-revenues-page .date-cell {
        direction: ltr;
        unicode-bidi: isolate;
        text-align: center;
        white-space: nowrap;
      }

      .employee-revenues-page input[type="date"],
      .employee-revenues-page .date-text {
        direction: ltr;
        unicode-bidi: isolate;
        text-align: center;
      }

      .employee-revenues-page .date-text {
        display: inline-block;
      }

      .employee-revenues-page .summary-box {
        border: 1px solid rgba(var(--bs-border-color-rgb), .5);
        border-radius: .5rem;
        padding: .85rem 1rem;
        background: rgba(var(--bs-body-bg-rgb), .45);
      }

      .employee-revenues-page .summary-value {
        direction: ltr;
        font-size: 1.15rem;
        font-weight: 700;
      }

      .employee-revenues-page .employee-name {
        min-width: 180px;
      }

      .employee-revenues-page .print-title {
        display: none;
      }

      @media (max-width: 767.98px) {
        .employee-revenues-page {
          margin-inline: -.75rem;
        }

        .employee-revenues-page .card {
          border-radius: 0;
        }

        .employee-revenues-page .report-table {
          min-width: 920px;
        }
      }

      @media print {
        @page {
          size: A5 portrait;
          margin: 6mm;
        }

        html,
        body {
          width: 148mm;
          min-height: 210mm;
          background: #fff !important;
          color: #111 !important;
          font-size: 7pt;
          line-height: 1.25;
          -webkit-print-color-adjust: exact;
          print-color-adjust: exact;
        }

        body * {
          visibility: hidden;
        }

        .print-area,
        .print-area * {
          visibility: visible;
        }

        .print-area {
          position: absolute;
          inset: 0;
          width: 100%;
          background: #fff;
          color: #000;
          padding: 0;
        }

        .employee-revenues-page {
          margin: 0 !important;
        }

        .no-print {
          display: none !important;
        }

        .employee-revenues-page .print-title {
          display: block;
          margin-bottom: 4mm !important;
          padding-bottom: 3mm;
          border-bottom: 1px solid #222;
        }

        .employee-revenues-page .print-title h4 {
          margin: 0 0 1.5mm !important;
          font-size: 11pt;
          font-weight: 800;
        }

        .employee-revenues-page .print-title div {
          font-size: 6.8pt;
          color: #444;
        }

        .employee-revenues-page .row {
          --bs-gutter-x: 1.3mm;
          --bs-gutter-y: 1.3mm;
          display: flex !important;
          flex-wrap: nowrap !important;
        }

        .employee-revenues-page .row > [class*="col-"] {
          flex: 1 1 0 !important;
          width: auto !important;
          max-width: none !important;
        }

        .employee-revenues-page .summary-box {
          border: 1px solid #c8ced6 !important;
          border-radius: 2mm;
          padding: 1.6mm 2mm;
          background: #f7f9fb !important;
          page-break-inside: avoid;
        }

        .employee-revenues-page .summary-box .small {
          font-size: 5.8pt;
          color: #555 !important;
          margin-bottom: 1mm !important;
        }

        .employee-revenues-page .summary-value {
          font-size: 7.2pt;
          color: #111 !important;
        }

        .employee-revenues-page .card {
          border: 0;
          box-shadow: none;
          margin-bottom: 4mm !important;
          page-break-inside: auto;
          background: transparent !important;
        }

        .employee-revenues-page .card-header {
          display: block !important;
          padding: 0 0 2mm !important;
          margin-bottom: 2mm;
          border-bottom: 1px solid #d8dde3 !important;
        }

        .employee-revenues-page .card-header h5 {
          margin: 0 0 1mm !important;
          font-size: 8.5pt;
          font-weight: 800;
        }

        .employee-revenues-page .card-header small {
          color: #555 !important;
          font-size: 6.5pt;
        }

        .employee-revenues-page .employee-print-summary {
          padding: 0 0 3mm !important;
          margin-bottom: 3mm;
          border-bottom: 1px solid #d8dde3 !important;
          page-break-inside: avoid;
        }

        .employee-revenues-page .employee-print-summary .summary-box {
          min-height: 0;
        }

        .employee-revenues-page .employee-print-summary .summary-value {
          font-size: 6.8pt;
          line-height: 1.25;
        }

        .employee-revenues-page .table-responsive {
          overflow: visible !important;
        }

        .employee-revenues-page .report-table {
          width: 100% !important;
          min-width: 0 !important;
          border-collapse: collapse !important;
          table-layout: fixed;
          font-size: 5.7pt;
        }

        .employee-revenues-page .report-table th {
          background: #edf1f5 !important;
          color: #000 !important;
          border: 1px solid #9fa8b3 !important;
          padding: .9mm .55mm !important;
          font-weight: 800;
          white-space: normal;
          line-height: 1.25;
        }

        .employee-revenues-page .report-table td {
          border: 1px solid #c8ced6 !important;
          padding: .8mm .5mm !important;
          color: #111 !important;
          background: #fff !important;
          vertical-align: middle;
          word-break: break-word;
        }

        .employee-revenues-page .report-table tr {
          page-break-inside: avoid;
        }

        .employee-revenues-page .employee-name {
          min-width: 0;
          width: 22%;
        }

        .employee-revenues-page .amount-cell,
        .employee-revenues-page .date-cell {
          white-space: nowrap;
          font-size: 5.6pt;
        }

        .employee-revenues-page .badge {
          border: 1px solid #b5bdc7;
          background: #f3f5f7 !important;
          color: #111 !important;
          padding: .8mm 1.3mm;
          font-size: 5.5pt;
        }

        .employee-revenues-page .text-success,
        .employee-revenues-page .text-danger {
          color: #111 !important;
        }

        .employee-revenues-page .text-muted {
          color: #555 !important;
        }

        .employee-revenues-page .table-active > * {
          background: #f5f7fa !important;
        }

        body.print-details-only .employee-revenues-page .summary-section,
        body.print-details-only .employee-revenues-page .employees-list-card {
          display: none !important;
        }

        body.print-details-only .employee-revenues-page .employee-detail-card {
          display: block !important;
        }
      }
    </style>
  @endsection

  @include('_partials/_alerts/alert-general')

  <div class="employee-revenues-page">
    <div class="card mb-4 no-print">
      <div class="card-header border-bottom">
        <h5 class="mb-0">إيرادات الموظفين</h5>
        <small class="text-muted">تقرير شهري أو حسب تاريخ محدد للإيرادات والتيبس لكل موظف</small>
      </div>
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-lg-2 col-md-4">
            <label class="form-label">الشهر</label>
            <input wire:model.defer="selectedMonth" type="month" class="form-control @error('selectedMonth') is-invalid @enderror">
            @error('selectedMonth')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-2 col-md-4">
            <button wire:click="applyMonth" type="button" class="btn btn-label-primary w-100">
              <i class="ti ti-calendar me-1"></i>
              عرض الشهر
            </button>
          </div>
          <div class="col-lg-2 col-md-4">
            <label class="form-label">من تاريخ</label>
              <input wire:model.defer="fromDate" type="date" dir="ltr" lang="en" class="form-control @error('fromDate') is-invalid @enderror">
            @error('fromDate')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-2 col-md-4">
            <label class="form-label">إلى تاريخ</label>
              <input wire:model.defer="toDate" type="date" dir="ltr" lang="en" class="form-control @error('toDate') is-invalid @enderror">
            @error('toDate')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-2 col-md-4">
            <label class="form-label">القسم</label>
            <select wire:model.defer="account" class="form-select @error('account') is-invalid @enderror">
              <option value="all">كل الأقسام</option>
              @foreach($accountLabels as $accountKey => $accountLabel)
                <option value="{{ $accountKey }}">{{ $accountLabel }}</option>
              @endforeach
            </select>
            @error('account')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-2 col-md-4">
            <button wire:click="applyFilters" type="button" class="btn btn-primary w-100">
              <i class="ti ti-filter me-1"></i>
              تطبيق
            </button>
          </div>
          <div class="col-lg-4 col-md-6">
            <label class="form-label">بحث عن موظف</label>
            <input wire:model.live.debounce.350ms="search" type="search" class="form-control" placeholder="اسم الموظف">
          </div>
          <div class="col-lg-2 col-md-4">
            <button onclick="printEmployeeRevenueReport('full')" type="button" class="btn btn-label-secondary w-100">
              <i class="ti ti-printer me-1"></i>
              طباعة التقرير
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="print-area">
      <div class="print-title text-center mb-3">
        <h4 class="mb-1">تقرير إيرادات الموظفين</h4>
        <div>
          <span class="date-text">{{ \Carbon\Carbon::parse($fromDate)->format('d-m-Y') }}</span>
          إلى
          <span class="date-text">{{ \Carbon\Carbon::parse($toDate)->format('d-m-Y') }}</span>
        </div>
      </div>

      <div class="row g-3 mb-4 summary-section">
        <div class="col-lg-4 col-md-6">
          <div class="summary-box">
            <div class="text-muted small mb-1">إجمالي الإيرادات</div>
            <div class="summary-value text-success">AED {{ number_format($totals['revenue'], 2) }}</div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="summary-box">
          <div class="text-muted small mb-1">إجمالي التيبس / الإكرامية</div>
          <div class="summary-value text-warning">AED {{ number_format($totals['tip'], 2) }}</div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="summary-box">
          <div class="text-muted small mb-1">إجمالي دخل الموظفين</div>
          <div class="summary-value">AED {{ number_format($totals['balance'], 2) }}</div>
          </div>
        </div>
      </div>

      <div class="card mb-4 employees-list-card">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-0">قائمة الموظفين</h5>
            <small class="text-muted">
              <span class="date-text">{{ \Carbon\Carbon::parse($fromDate)->format('d-m-Y') }}</span>
              إلى
              <span class="date-text">{{ \Carbon\Carbon::parse($toDate)->format('d-m-Y') }}</span>
            </small>
          </div>
          <span class="badge bg-label-primary no-print">{{ count($rows) }} موظف</span>
        </div>
        <div class="table-responsive">
          <table class="table report-table align-middle mb-0">
            <thead>
              <tr>
                <th class="text-center">الموظف</th>
                <th class="text-center">المنصب</th>
                <th class="text-center">عدد الإيرادات</th>
                <th class="text-center">إجمالي الإيرادات</th>
                <th class="text-center">عدد التيبس</th>
                <th class="text-center">إجمالي التيبس</th>
                <th class="text-center">إجمالي الدخل</th>
                <th class="text-center no-print">التفاصيل</th>
              </tr>
            </thead>
            <tbody>
              @forelse($rows as $row)
                <tr @class(['table-active' => $selectedEmployeeKey === $row['key']])>
                  <td class="employee-name">
                    <div class="fw-semibold">{{ $row['name'] }}</div>
                  </td>
                  <td class="text-center">{{ $row['position'] }}</td>
                  <td class="amount-cell">{{ number_format($row['revenue_count']) }}</td>
                  <td class="amount-cell text-success">AED {{ number_format($row['revenue'], 2) }}</td>
                  <td class="amount-cell">{{ number_format($row['tip_count']) }}</td>
                  <td class="amount-cell text-warning">AED {{ number_format($row['tip'], 2) }}</td>
                  <td class="amount-cell fw-semibold">AED {{ number_format($row['balance'], 2) }}</td>
                  <td class="text-center no-print">
                    <button wire:click="selectEmployee('{{ $row['key'] }}')" type="button" class="btn btn-sm btn-label-info">
                      <i class="ti ti-list-details me-1"></i>
                      عرض
                    </button>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center text-muted py-5">لا توجد بيانات في الفترة المحددة.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      @if($selectedEmployeeKey)
        @php($selectedRow = collect($rows)->firstWhere('key', $selectedEmployeeKey))
        <div class="card employee-detail-card">
          <div class="card-header border-bottom d-flex justify-content-between align-items-center">
            <div>
              <h5 class="mb-0">تفاصيل {{ $this->selectedEmployeeName() }}</h5>
              <small class="text-muted">الإيرادات والتيبس خلال الفترة المحددة</small>
            </div>
            <button onclick="printEmployeeRevenueReport('details')" type="button" class="btn btn-sm btn-label-secondary no-print">
              <i class="ti ti-printer me-1"></i>
              طباعة التفاصيل
            </button>
          </div>
          @if($selectedRow)
            <div class="card-body employee-print-summary border-bottom">
              <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                  <div class="summary-box">
                    <div class="text-muted small mb-1">الشهر / الفترة</div>
                    <div class="summary-value">
                      <span class="date-text">{{ \Carbon\Carbon::parse($fromDate)->format('d-m-Y') }}</span>
                      -
                      <span class="date-text">{{ \Carbon\Carbon::parse($toDate)->format('d-m-Y') }}</span>
                    </div>
                  </div>
                </div>
                <div class="col-lg-3 col-md-6">
                  <div class="summary-box">
                    <div class="text-muted small mb-1">مجموع الإيرادات</div>
                    <div class="summary-value text-success">AED {{ number_format($selectedRow['revenue'], 2) }}</div>
                  </div>
                </div>
                <div class="col-lg-3 col-md-6">
                  <div class="summary-box">
                    <div class="text-muted small mb-1">دخل الصالون / التيبس</div>
                    <div class="summary-value text-warning">AED {{ number_format($selectedRow['tip'], 2) }}</div>
                  </div>
                </div>
                <div class="col-lg-3 col-md-6">
                  <div class="summary-box">
                    <div class="text-muted small mb-1">الإجمالي</div>
                    <div class="summary-value">AED {{ number_format($selectedRow['balance'], 2) }}</div>
                  </div>
                </div>
              </div>
            </div>
          @endif
          <div class="table-responsive">
            <table class="table report-table align-middle mb-0">
              <thead>
                <tr>
                  <th class="text-center">التاريخ</th>
                  <th class="text-center">القسم</th>
                  <th class="text-center">النوع</th>
                  <th class="text-center">الخدمة / البيان</th>
                  <th class="text-center">طريقة الدفع</th>
                  <th class="text-center">المبلغ</th>
                  <th class="text-center">ملاحظة</th>
                </tr>
              </thead>
              <tbody>
                @forelse($selectedTransactions as $transaction)
                  @php($isRevenue = $transaction->type === 'revenue')
                  <tr>
                    <td class="date-cell">{{ \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') }}</td>
                    <td class="text-center">{{ $accountLabels[$transaction->account] ?? $transaction->account }}</td>
                    <td class="text-center">
                      <span class="badge bg-label-{{ $isRevenue ? 'success' : 'danger' }}">
                        {{ $isRevenue ? 'إيراد' : 'تيبس' }}
                      </span>
                    </td>
                    <td class="text-center">{{ $isRevenue ? ($transaction->service ?: '---') : ($transaction->withdrawn_to ?: '---') }}</td>
                    <td class="text-center">{{ $isRevenue ? ($transaction->payment_method === 'visa' ? 'Visa' : 'Cash') : '---' }}</td>
                    <td class="amount-cell {{ $isRevenue ? 'text-success' : 'text-warning' }}">AED {{ number_format((float) $transaction->amount, 2) }}</td>
                    <td>{{ $transaction->note ?: '---' }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center text-muted py-5">لا توجد تفاصيل لهذا الموظف.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      @endif
    </div>
  </div>
</div>

@push('custom-scripts')
  <script>
    function printEmployeeRevenueReport(mode) {
      document.body.classList.toggle('print-details-only', mode === 'details');
      window.print();
    }

    window.addEventListener('afterprint', () => {
      document.body.classList.remove('print-details-only');
    });
  </script>
@endpush
