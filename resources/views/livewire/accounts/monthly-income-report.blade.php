<div dir="rtl">
  @section('title', 'تقرير الشهور - '.$accountName)

  @section('page-style')
    <style>
      .monthly-income-report .amount-cell,
      .monthly-income-report .date-cell {
        direction: ltr;
        unicode-bidi: isolate;
        text-align: center;
        white-space: nowrap;
      }

      .monthly-income-report .report-panel {
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .5rem;
        background: rgba(var(--bs-body-bg-rgb), .42);
      }

      .monthly-income-report .filter-panel {
        margin-top: 1rem;
        padding: .85rem;
        border: 1px solid rgba(var(--bs-border-color-rgb), .38);
        border-radius: .5rem;
        background: rgba(var(--bs-body-bg-rgb), .32);
      }

      .monthly-income-report .filter-switch {
        min-height: 100%;
        padding: .7rem .9rem;
        border: 1px solid rgba(var(--bs-border-color-rgb), .35);
        border-radius: .5rem;
        background: rgba(var(--bs-body-bg-rgb), .32);
      }

      .monthly-income-report .filter-switch .form-check-input {
        float: none;
        margin-left: .6rem;
      }

      .monthly-income-report .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
      }

      .monthly-income-report .summary-card {
        min-height: 132px;
        padding: 1rem;
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .5rem;
        background: rgba(var(--bs-body-bg-rgb), .45);
      }

      .monthly-income-report .summary-label {
        color: var(--bs-secondary-color);
        font-size: .82rem;
      }

      .monthly-income-report .summary-value {
        direction: ltr;
        margin-top: .4rem;
        font-size: 1.35rem;
        font-weight: 800;
      }

      .monthly-income-report .summary-lines {
        display: grid;
        gap: .4rem;
        margin-top: .75rem;
        padding-top: .7rem;
        border-top: 1px solid rgba(var(--bs-border-color-rgb), .3);
      }

      .monthly-income-report .summary-line {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        color: var(--bs-secondary-color);
        font-size: .78rem;
      }

      .monthly-income-report .summary-line strong {
        direction: ltr;
        color: var(--bs-heading-color);
        white-space: nowrap;
      }

      .monthly-income-report .report-table th {
        white-space: nowrap;
        vertical-align: middle;
      }

      .monthly-income-report .report-table td {
        vertical-align: middle;
      }

      .monthly-income-report .detail-name {
        min-width: 210px;
        white-space: normal;
      }

      .monthly-income-report .details-table {
        min-width: 1080px;
      }

      .monthly-income-report .detail-note {
        min-width: 180px;
        max-width: 280px;
        white-space: normal;
      }

      .monthly-income-report .details-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .35rem;
        min-width: 72px;
        white-space: nowrap;
      }

      .monthly-income-report .details-action {
        width: 88px;
        min-width: 88px;
      }

      .monthly-income-report .service-mobile-cards {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
        padding: .85rem;
      }

      .monthly-income-report .service-mobile-card {
        padding: .85rem;
        border: 1px solid rgba(var(--bs-border-color-rgb), .35);
        border-radius: .5rem;
        background: rgba(var(--bs-body-bg-rgb), .32);
      }

      .monthly-income-report .services-table-wrap {
        display: none;
      }

      .monthly-income-report .service-mobile-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .75rem;
      }

      .monthly-income-report .service-mobile-title {
        min-width: 0;
        font-weight: 700;
        overflow-wrap: anywhere;
      }

      .monthly-income-report .service-mobile-lines {
        display: grid;
        gap: .45rem;
      }

      .monthly-income-report .print-only {
        display: none;
      }

      @media (max-width: 991.98px) {
        .monthly-income-report .summary-grid {
          grid-template-columns: repeat(2, minmax(0, 1fr));
        }
      }

      @media (max-width: 767.98px) {
        .monthly-income-report {
          margin-inline: -.75rem;
        }

        .monthly-income-report .card {
          border-radius: 0;
        }

        .monthly-income-report .card-header,
        .monthly-income-report .filter-row {
          align-items: stretch !important;
          flex-direction: column;
        }

        .monthly-income-report .summary-grid {
          grid-template-columns: 1fr;
        }

        .monthly-income-report .summary-card {
          min-height: 0;
        }

        .monthly-income-report .filter-panel {
          margin-top: .75rem;
          padding: .65rem;
        }

        .monthly-income-report .filter-switch {
          padding: .65rem;
        }

        .monthly-income-report .table-responsive {
          scrollbar-width: thin;
          scrollbar-color: rgba(var(--bs-primary-rgb), .65) rgba(var(--bs-border-color-rgb), .25);
        }

        .monthly-income-report .report-table {
          min-width: 920px;
        }

        .monthly-income-report .service-mobile-cards {
          grid-template-columns: 1fr;
          padding: .65rem;
        }

        .monthly-income-report .filter-actions {
          display: grid !important;
          grid-template-columns: 44px minmax(0, 1fr) 44px;
          width: 100%;
        }

        .monthly-income-report .filter-actions .btn-icon {
          width: 44px;
          height: 44px;
        }

        .monthly-income-report .filter-actions .btn-label-secondary,
        .monthly-income-report .filter-actions .btn-primary {
          grid-column: 1 / -1;
          width: 100%;
        }
      }

      @media print {
        @page {
          size: A4 landscape;
          margin: 8mm;
        }

        body {
          background: #fff !important;
          color: #111 !important;
          font-size: 8pt;
          -webkit-print-color-adjust: exact;
          print-color-adjust: exact;
        }

        body * {
          visibility: hidden;
        }

        .monthly-income-report,
        .monthly-income-report * {
          visibility: visible;
        }

        .monthly-income-report {
          position: absolute;
          inset: 0;
          margin: 0 !important;
          background: #fff !important;
          color: #111 !important;
        }

        .monthly-income-report .no-print {
          display: none !important;
        }

        .monthly-income-report .print-only {
          display: block !important;
        }

        .monthly-income-report .print-title {
          margin-bottom: 5mm;
          padding-bottom: 3mm;
          border-bottom: 1px solid #111;
          text-align: center;
        }

        .monthly-income-report .card,
        .monthly-income-report .summary-card,
        .monthly-income-report .report-panel {
          border: 0 !important;
          box-shadow: none !important;
          background: transparent !important;
        }

        .monthly-income-report .card {
          margin-bottom: 5mm !important;
          page-break-inside: auto;
        }

        .monthly-income-report .card-header,
        .monthly-income-report .card-body {
          padding: 0 0 3mm !important;
        }

        .monthly-income-report .summary-grid {
          grid-template-columns: repeat(4, 1fr) !important;
          gap: 2mm !important;
        }

        .monthly-income-report .summary-card {
          padding: 2mm !important;
          border: 1px solid #ccd3dc !important;
          background: #f7f9fb !important;
        }

        .monthly-income-report .summary-value {
          font-size: 9pt !important;
          color: #111 !important;
        }

        .monthly-income-report .table-responsive {
          overflow: visible !important;
        }

        .monthly-income-report .services-table-wrap {
          display: block !important;
        }

        .monthly-income-report .service-mobile-cards {
          display: none !important;
        }

        .monthly-income-report .report-table {
          width: 100% !important;
          min-width: 0 !important;
          table-layout: fixed;
          border-collapse: collapse !important;
          font-size: 6.5pt;
        }

        .monthly-income-report .report-table th {
          padding: 1mm !important;
          border: 1px solid #9fa8b3 !important;
          background: #edf1f5 !important;
          color: #111 !important;
          white-space: normal;
        }

        .monthly-income-report .report-table td {
          padding: 1mm !important;
          border: 1px solid #c8ced6 !important;
          color: #111 !important;
          background: #fff !important;
          word-break: break-word;
        }

        .monthly-income-report .text-success,
        .monthly-income-report .text-danger,
        .monthly-income-report .text-warning,
        .monthly-income-report .text-muted {
          color: #111 !important;
        }
      }
    </style>
  @endsection

  <div class="monthly-income-report">
    <div class="print-only print-title">
      <h4 class="mb-1">تقرير الشهور - {{ $accountName }}</h4>
      <div>من <span class="date-cell">{{ \Carbon\Carbon::parse($fromDate)->format('d-m-Y') }}</span> إلى <span class="date-cell">{{ \Carbon\Carbon::parse($toDate)->format('d-m-Y') }}</span></div>
    </div>

    <div class="card mb-4 no-print">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center gap-3">
        <div>
          <h5 class="mb-0">تقرير الشهور - {{ $accountName }}</h5>
          <small class="text-muted">تقرير كامل للدخل والمبيعات والمصاريف والرواتب حسب الفترة المختارة.</small>
        </div>
        <button onclick="window.print()" type="button" class="btn btn-primary">
          <i class="ti ti-printer me-1"></i>
          طباعة للضرائب
        </button>
      </div>
      <div class="card-body">
        <div class="row g-3 align-items-end filter-row">
          <div class="col-lg-3 col-md-6">
            <label class="form-label">نهاية التقرير</label>
            <select wire:model.live="selectedMonth" class="form-select date-cell">
              @foreach($availableMonths as $month)
                <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-lg-3 col-md-6">
            <label class="form-label">المدة</label>
            <select wire:model.live="period" class="form-select">
              @foreach($periodOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-lg-6">
            <div class="d-flex align-items-center gap-2 filter-actions">
              <button wire:click="showPreviousMonth" type="button" class="btn btn-label-primary btn-icon" title="الشهر السابق" @disabled(! $this->canShowPreviousMonth())>
                <i class="ti ti-chevron-right"></i>
              </button>
              <button wire:click="showCurrentMonth" type="button" class="btn btn-label-secondary">
                الشهر الحالي
              </button>
              <button wire:click="showNextMonth" type="button" class="btn btn-label-primary btn-icon" title="الشهر التالي" @disabled(! $this->canShowNextMonth())>
                <i class="ti ti-chevron-left"></i>
              </button>
              <div class="ms-auto text-muted small">
                من <span class="date-cell">{{ \Carbon\Carbon::parse($fromDate)->format('d-m-Y') }}</span>
                إلى <span class="date-cell">{{ \Carbon\Carbon::parse($toDate)->format('d-m-Y') }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="filter-panel">
          <div class="row g-3 align-items-end">
            <div class="col-lg-3 col-md-6">
              <label class="form-label">طريقة الدفع</label>
              <select wire:model.live="paymentFilter" class="form-select">
                @foreach($paymentOptions as $value => $label)
                  <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-lg-3 col-md-6">
              <label class="form-label">نوع الدخل</label>
              <select wire:model.live="revenueFilter" class="form-select">
                @foreach($revenueFilterOptions as $value => $label)
                  <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-lg-6">
              <div class="form-check form-switch filter-switch mb-0">
                <input
                  wire:model.live="excludePayrollTransfers"
                  class="form-check-input"
                  type="checkbox"
                  role="switch"
                  id="excludePayrollTransfers"
                >
                <label class="form-check-label fw-semibold" for="excludePayrollTransfers">
                  استبعاد تحويلات الرواتب من الدخل
                </label>
                <div class="form-text">
                  مثل Payroll transfer 2026-05، لأنها تحويل داخلي وليست مبيعات فعلية.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="summary-grid mb-4">
      <div class="summary-card">
        <div class="summary-label">إجمالي دخل المبيعات</div>
        <div class="summary-value text-success">AED {{ number_format($totals['revenue'], 2) }}</div>
        <div class="summary-lines">
          <div class="summary-line"><span>كاش</span><strong>AED {{ number_format($totals['cash'], 2) }}</strong></div>
          <div class="summary-line"><span>فيزا</span><strong>AED {{ number_format($totals['visa'], 2) }}</strong></div>
          @if($totals['payroll_transfers'] > 0)
            <div class="summary-line">
              <span>{{ $excludePayrollTransfers ? 'تحويلات مستبعدة' : 'تحويلات داخلة' }}</span>
              <strong>AED {{ number_format($excludePayrollTransfers ? $totals['excluded_payroll_transfers'] : $totals['payroll_transfers'], 2) }}</strong>
            </div>
          @endif
        </div>
      </div>
      <div class="summary-card">
        <div class="summary-label">المنتجات من الجرد</div>
        <div class="summary-value">AED {{ number_format($totals['product_revenue'], 2) }}</div>
        <div class="summary-lines">
          <div class="summary-line"><span>كمية مباعة</span><strong>{{ number_format($totals['product_quantity'], 2) }}</strong></div>
        </div>
      </div>
      <div class="summary-card">
        <div class="summary-label">الخدمات</div>
        <div class="summary-value">AED {{ number_format($totals['service_revenue'], 2) }}</div>
        <div class="summary-lines">
          <div class="summary-line"><span>عدد الخدمات</span><strong>{{ number_format($totals['service_count']) }}</strong></div>
        </div>
      </div>
      <div class="summary-card">
        <div class="summary-label">الصافي بعد المصاريف</div>
        <div class="summary-value {{ $totals['net_income'] >= 0 ? 'text-success' : 'text-danger' }}">AED {{ number_format($totals['net_income'], 2) }}</div>
        <div class="summary-lines">
          <div class="summary-line"><span>المصاريف</span><strong>AED {{ number_format($totals['expenses'], 2) }}</strong></div>
          <div class="summary-line"><span>الرواتب</span><strong>AED {{ number_format($totals['salaries'], 2) }}</strong></div>
        </div>
      </div>
    </div>

    @if($totals['payroll_transfers'] > 0)
      <div class="alert {{ $excludePayrollTransfers ? 'alert-info' : 'alert-warning' }} no-print">
        تحويلات الرواتب الداخلية خلال الفترة:
        <strong class="amount-cell">AED {{ number_format($totals['payroll_transfers'], 2) }}</strong>
        @if($excludePayrollTransfers)
          تم استبعادها من الدخل لأنها تحويل داخلي وليست مبيعات فعلية.
        @else
          داخلة حاليا في الدخل بسبب إلغاء فلتر الاستبعاد.
        @endif
      </div>
    @endif

    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h5 class="mb-0">ملخص الشهور</h5>
      </div>
      <div class="table-responsive">
        <table class="table report-table align-middle mb-0">
          <thead>
            <tr>
              <th>الشهر</th>
              <th class="text-center">الدخل</th>
              <th class="text-center">كاش</th>
              <th class="text-center">فيزا</th>
              <th class="text-center">تحويل رواتب</th>
              <th class="text-center">منتجات</th>
              <th class="text-center">خدمات</th>
              <th class="text-center">المشتريات</th>
              <th class="text-center">سحب نقدي</th>
              <th class="text-center">تيبس</th>
              <th class="text-center">سلف</th>
              <th class="text-center">رواتب</th>
              <th class="text-center">كل المصاريف</th>
              <th class="text-center">الصافي</th>
              <th class="text-center no-print">تفاصيل</th>
            </tr>
          </thead>
          <tbody>
            @forelse($monthlyRows as $row)
              <tr>
                <td>{{ $row['label'] }}</td>
                <td class="amount-cell text-success fw-semibold">AED {{ number_format($row['revenue'], 2) }}</td>
                <td class="amount-cell">AED {{ number_format($row['cash'], 2) }}</td>
                <td class="amount-cell">AED {{ number_format($row['visa'], 2) }}</td>
                <td class="amount-cell text-warning">AED {{ number_format($row['payroll_transfers'], 2) }}</td>
                <td class="amount-cell">AED {{ number_format($row['products'], 2) }}</td>
                <td class="amount-cell">AED {{ number_format($row['services'], 2) }}</td>
                <td class="amount-cell">AED {{ number_format($row['purchases'], 2) }}</td>
                <td class="amount-cell">AED {{ number_format($row['cash_withdrawals'], 2) }}</td>
                <td class="amount-cell">AED {{ number_format($row['tips'], 2) }}</td>
                <td class="amount-cell">AED {{ number_format($row['advances'], 2) }}</td>
                <td class="amount-cell">AED {{ number_format($row['salaries'], 2) }}</td>
                <td class="amount-cell text-danger">AED {{ number_format($row['expenses'], 2) }}</td>
                <td class="amount-cell {{ $row['net_income'] >= 0 ? 'text-success' : 'text-danger' }} fw-semibold">AED {{ number_format($row['net_income'], 2) }}</td>
                <td class="text-center no-print">
                  <button wire:click="showTransactionDetails(@js($row['detail_title']), '{{ $row['ids'] }}')" type="button" class="btn btn-sm btn-label-primary details-button" title="عرض التفاصيل" @disabled(blank($row['ids']))>
                    <i class="ti ti-eye"></i>
                    <span>عرض</span>
                  </button>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="15" class="text-center text-muted py-5">لا توجد بيانات في الفترة المختارة.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="row g-4 mb-4">
      <div class="col-xl-7">
        <div class="card h-100">
          <div class="card-header border-bottom">
            <h5 class="mb-0">مبيعات المنتجات من الجرد</h5>
          </div>
          <div class="table-responsive">
            <table class="table report-table align-middle mb-0">
              <thead>
                <tr>
                  <th>المنتج</th>
                  <th class="text-center">SKU</th>
                  <th class="text-center">الكمية</th>
                  <th class="text-center">سعر البيع</th>
                  <th class="text-center">عدد العمليات</th>
                  <th class="text-center">الإجمالي</th>
                  <th class="text-center no-print">تفاصيل</th>
                </tr>
              </thead>
              <tbody>
                @forelse($productRows as $row)
                  <tr>
                    <td class="detail-name">{{ $row['name'] }}</td>
                    <td class="text-center">{{ $row['sku'] }}</td>
                    <td class="amount-cell">{{ $this->quantityLabel($row['quantity'], $row['unit']) }}</td>
                    <td class="amount-cell">AED {{ number_format($row['unit_price'], 2) }}</td>
                    <td class="amount-cell">{{ number_format($row['count']) }}</td>
                    <td class="amount-cell text-success fw-semibold">AED {{ number_format($row['total'], 2) }}</td>
                    <td class="text-center no-print">
                      <button wire:click="showTransactionDetails(@js($row['detail_title']), '{{ $row['ids'] }}')" type="button" class="btn btn-sm btn-label-primary details-button" title="عرض التفاصيل">
                        <i class="ti ti-eye"></i>
                        <span>عرض</span>
                      </button>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center text-muted py-5">لا توجد مبيعات منتجات في الفترة المختارة.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-xl-5">
        <div class="card h-100">
          <div class="card-header border-bottom">
            <h5 class="mb-0">تحليل المصاريف</h5>
          </div>
          <div class="table-responsive">
            <table class="table report-table align-middle mb-0">
              <thead>
                <tr>
                  <th>النوع</th>
                  <th class="text-center">عدد</th>
                  <th class="text-center">الإجمالي</th>
                  <th class="text-center no-print">تفاصيل</th>
                </tr>
              </thead>
              <tbody>
                @foreach($expenseRows as $row)
                  <tr>
                    <td>{{ $row['label'] }}</td>
                    <td class="amount-cell">{{ number_format($row['count']) }}</td>
                    <td class="amount-cell {{ $row['kind'] === 'salary' ? 'text-warning' : 'text-danger' }}">AED {{ number_format($row['total'], 2) }}</td>
                    <td class="text-center no-print">
                      <button wire:click="showTransactionDetails(@js($row['detail_title']), '{{ $row['ids'] }}')" type="button" class="btn btn-sm btn-label-primary details-button" title="عرض التفاصيل" @disabled(blank($row['ids']))>
                        <i class="ti ti-eye"></i>
                        <span>عرض</span>
                      </button>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-xl-7">
        <div class="card h-100">
          <div class="card-header border-bottom">
            <h5 class="mb-0">الخدمات والأسعار</h5>
          </div>
          <div class="table-responsive services-table-wrap">
            <table class="table report-table align-middle mb-0">
              <thead>
                <tr>
                  <th>الخدمة</th>
                  <th class="text-center no-print details-action">عرض</th>
                  <th class="text-center">السعر</th>
                  <th class="text-center">عدد</th>
                  <th class="text-center">كاش</th>
                  <th class="text-center">فيزا</th>
                  <th class="text-center">الإجمالي</th>
                </tr>
              </thead>
              <tbody>
                @forelse($serviceRows as $row)
                  <tr>
                    <td class="detail-name">{{ $row['service'] }}</td>
                    <td class="text-center no-print">
                      <button wire:click="showTransactionDetails(@js($row['detail_title']), '{{ $row['ids'] }}')" type="button" class="btn btn-sm btn-label-primary details-button" title="عرض التفاصيل">
                        <i class="ti ti-eye"></i>
                        <span>عرض</span>
                      </button>
                    </td>
                    <td class="amount-cell">AED {{ number_format($row['price'], 2) }}</td>
                    <td class="amount-cell">{{ number_format($row['count']) }}</td>
                    <td class="amount-cell">AED {{ number_format($row['cash'], 2) }}</td>
                    <td class="amount-cell">AED {{ number_format($row['visa'], 2) }}</td>
                    <td class="amount-cell text-success fw-semibold">AED {{ number_format($row['total'], 2) }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center text-muted py-5">لا توجد خدمات في الفترة المختارة.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <div class="service-mobile-cards">
            @php
              $visibleServiceRows = array_slice($serviceRows, 0, $serviceRowsLimit);
              $hasMoreServiceRows = count($serviceRows) > count($visibleServiceRows);
            @endphp

            @forelse($visibleServiceRows as $row)
              <div class="service-mobile-card">
                <div class="service-mobile-head">
                  <div class="service-mobile-title">{{ $row['service'] }}</div>
                  <button wire:click="showTransactionDetails(@js($row['detail_title']), '{{ $row['ids'] }}')" type="button" class="btn btn-sm btn-label-primary details-button" title="عرض التفاصيل">
                    <i class="ti ti-eye"></i>
                    <span>عرض</span>
                  </button>
                </div>
                <div class="service-mobile-lines">
                  <div class="summary-line"><span>السعر</span><strong>AED {{ number_format($row['price'], 2) }}</strong></div>
                  <div class="summary-line"><span>عدد</span><strong>{{ number_format($row['count']) }}</strong></div>
                  <div class="summary-line"><span>كاش</span><strong>AED {{ number_format($row['cash'], 2) }}</strong></div>
                  <div class="summary-line"><span>فيزا</span><strong>AED {{ number_format($row['visa'], 2) }}</strong></div>
                  <div class="summary-line"><span>الإجمالي</span><strong class="text-success">AED {{ number_format($row['total'], 2) }}</strong></div>
                </div>
              </div>
            @empty
              <div class="text-center text-muted py-5">لا توجد خدمات في الفترة المختارة.</div>
            @endforelse
          </div>

          @if(count($serviceRows) > 12)
            <div class="d-flex justify-content-center align-items-center gap-2 flex-wrap p-3 pt-0 no-print">
              @if($hasMoreServiceRows)
                <button wire:click="showMoreServices" type="button" class="btn btn-label-primary">
                  <i class="ti ti-chevron-down me-1"></i>
                  عرض التالي
                </button>
              @endif

              @if(count($visibleServiceRows) > 12)
                <button wire:click="collapseServices" type="button" class="btn btn-label-secondary">
                  اختصار القائمة
                </button>
              @endif

              <span class="text-muted small">
                عرض {{ count($visibleServiceRows) }} من {{ count($serviceRows) }}
              </span>
            </div>
          @endif
        </div>
      </div>

      <div class="col-xl-5">
        <div class="card h-100">
          <div class="card-header border-bottom">
            <h5 class="mb-0">رواتب الموظفين المدفوعة</h5>
          </div>
          <div class="table-responsive">
            <table class="table report-table align-middle mb-0">
              <thead>
                <tr>
                  <th>شهر الراتب</th>
                  <th>الموظف</th>
                  <th class="text-center">عدد</th>
                  <th class="text-center">المدفوع</th>
                  <th class="text-center no-print">تفاصيل</th>
                </tr>
              </thead>
              <tbody>
                @forelse($salaryRows as $row)
                  <tr>
                    <td class="date-cell">{{ $row['month'] }}</td>
                    <td>{{ $row['employee'] }}</td>
                    <td class="amount-cell">{{ number_format($row['count']) }}</td>
                    <td class="amount-cell text-warning fw-semibold">AED {{ number_format($row['paid'], 2) }}</td>
                    <td class="text-center no-print">
                      <button wire:click="showTransactionDetails(@js($row['detail_title']), '{{ $row['ids'] }}')" type="button" class="btn btn-sm btn-label-primary details-button" title="عرض التفاصيل">
                        <i class="ti ti-eye"></i>
                        <span>عرض</span>
                      </button>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="text-center text-muted py-5">لا توجد رواتب مدفوعة في الفترة المختارة.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div wire:ignore.self class="modal fade no-print" id="reportDetailsModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h5 class="modal-title">{{ $detailTitle ?: 'تفاصيل العمليات' }}</h5>
              <small class="text-muted">
                {{ count($detailRows) }} عملية
                <span class="amount-cell ms-2">AED {{ number_format($detailTotal, 2) }}</span>
              </small>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="table-responsive">
              <table class="table report-table details-table align-middle mb-0">
                <thead>
                  <tr>
                    <th class="text-center">التاريخ</th>
                    <th class="text-center">اليوم</th>
                    <th class="text-center">الوقت</th>
                    <th class="text-center">النوع</th>
                    <th class="text-center">التصنيف</th>
                    <th>الوصف</th>
                    <th class="text-center">الموظف / المستلم</th>
                    <th class="text-center">الدفع</th>
                    <th class="text-center">الكمية</th>
                    <th class="text-center">سعر الوحدة</th>
                    <th class="text-center">المبلغ</th>
                    <th class="text-center">العميل</th>
                    <th>الملاحظة</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($detailRows as $row)
                    <tr>
                      <td class="date-cell">{{ $row['date'] }}</td>
                      <td class="text-center">{{ $row['day'] }}</td>
                      <td class="text-center">{{ $row['time'] }}</td>
                      <td class="text-center">
                        <span class="badge bg-label-{{ $row['type'] === 'دخل' ? 'success' : 'danger' }}">{{ $row['type'] }}</span>
                      </td>
                      <td class="text-center">{{ $row['kind'] }}</td>
                      <td class="detail-name">{{ $row['description'] }}</td>
                      <td class="text-center">{{ $row['employee'] }}</td>
                      <td class="text-center">{{ $row['payment_method'] }}</td>
                      <td class="amount-cell">{{ $row['quantity'] }}</td>
                      <td class="amount-cell">{{ $row['unit_price'] > 0 ? 'AED '.number_format($row['unit_price'], 2) : '---' }}</td>
                      <td class="amount-cell fw-semibold {{ $row['type'] === 'دخل' ? 'text-success' : 'text-danger' }}">AED {{ number_format($row['amount'], 2) }}</td>
                      <td class="text-center">{{ $row['customer'] }}</td>
                      <td class="detail-note">{{ $row['note'] }}</td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="13" class="text-center text-muted py-5">لا توجد عمليات لعرضها.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">إغلاق</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('custom-scripts')
  <script>
    window.addEventListener('openModal', event => {
      const element = document.querySelector(event.detail.elementId);

      if (!element) {
        return;
      }

      if (window.bootstrap?.Modal) {
        window.bootstrap.Modal.getOrCreateInstance(element).show();
        return;
      }

      $(element).modal('show');
    });
  </script>
@endpush
