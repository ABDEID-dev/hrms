<div dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
  @section('title', __('accounts.expenses').' '.$accountName)

  @section('page-style')
    <style>
      .expenses-page .sheet-table th {
        background: #7b1f0c;
        color: #fff;
        vertical-align: middle;
        white-space: nowrap;
      }

      .expenses-page .amount-cell {
        direction: ltr;
        text-align: center;
        white-space: nowrap;
      }

      .expenses-page .date-cell {
        direction: ltr;
        unicode-bidi: isolate;
        text-align: center;
        white-space: nowrap;
        min-width: 112px;
      }

      .expenses-page input[type="date"],
      .expenses-page .date-text {
        direction: ltr;
        unicode-bidi: isolate;
        text-align: center;
      }

      .expenses-page .date-text {
        display: inline-block;
      }

      .expenses-page .actions-cell {
        min-width: 110px;
      }

      .expenses-page .actions-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .35rem;
      }

      .expenses-page .invoice-image {
        display: block;
        width: 48px;
        height: 48px;
        margin: 0 auto;
        object-fit: contain;
        border: 1px solid rgba(var(--bs-border-color-rgb), .65);
        border-radius: .35rem;
        background: var(--bs-tertiary-bg);
      }

      .expenses-page .invoice-cell {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        white-space: nowrap;
      }

      .expenses-page .invoice-image-trigger {
        display: inline-flex;
        flex: 0 0 auto;
        margin: 0;
        padding: 0;
        border: 0;
        background: transparent;
        cursor: zoom-in;
      }

      .expenses-page .invoice-preview-dialog {
        max-width: min(92vw, 640px);
      }

      .expenses-page .invoice-preview-frame {
        display: grid;
        width: 100%;
        aspect-ratio: 1 / 1;
        place-items: center;
        overflow: hidden;
        border-radius: .35rem;
        background: var(--bs-tertiary-bg);
      }

      .expenses-page .invoice-preview-frame img {
        display: block;
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
        object-fit: contain;
      }

      .expenses-page .invoice-preview-actions {
        display: flex;
        justify-content: center;
        gap: .75rem;
        margin-top: 1rem;
      }

      .expenses-page .invoice-upload-panel {
        padding: .7rem .8rem;
        border: 1px solid rgba(var(--bs-border-color-rgb), .55);
        border-inline-start: 3px solid var(--bs-danger);
        border-radius: .45rem;
        background: rgba(var(--bs-body-bg-rgb), .35);
      }

      .expenses-page .invoice-upload-heading {
        display: flex;
        align-items: center;
        gap: .6rem;
        min-width: 0;
      }

      .expenses-page .invoice-upload-icon {
        display: grid;
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        place-items: center;
        border-radius: .35rem;
        color: var(--bs-danger);
        background: rgba(var(--bs-danger-rgb), .1);
        font-size: 1.1rem;
      }

      .expenses-page .invoice-upload-copy {
        flex: 1 1 auto;
        min-width: 0;
      }

      .expenses-page .invoice-upload-preview {
        display: flex;
        align-items: center;
        gap: .6rem;
        margin-top: .6rem;
        padding: .45rem .55rem;
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .35rem;
        background: rgba(var(--bs-body-bg-rgb), .45);
      }

      .expenses-page .invoice-upload-preview img {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        object-fit: contain;
        border: 1px solid rgba(var(--bs-border-color-rgb), .55);
        border-radius: .25rem;
        background: var(--bs-tertiary-bg);
      }

      .expenses-page .invoice-upload-preview-copy {
        min-width: 0;
        overflow-wrap: anywhere;
      }

      @media (max-width: 575.98px) {
        .expenses-page .invoice-preview-dialog {
          max-width: calc(100vw - 1rem);
          margin: .5rem auto;
        }

        .expenses-page .invoice-preview-frame {
          max-height: calc(100dvh - 190px);
        }
      }

      .expenses-page .mobile-scroll-hint {
        display: none;
      }

      .expenses-page .summary-box {
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .5rem;
        padding: .85rem 1rem;
        background: rgba(var(--bs-body-bg-rgb), .45);
      }

      .expenses-page .summary-value {
        direction: ltr;
        font-weight: 700;
        font-size: 1.15rem;
      }

      .expenses-page .today-summary {
        border-color: rgba(234, 84, 85, .45);
        background: rgba(234, 84, 85, .08);
      }

      .expenses-page .today-row > td {
        background: rgba(234, 84, 85, .07);
      }

      .expenses-page .table-section-row td {
        background: rgba(var(--bs-danger-rgb), .12);
        color: var(--bs-heading-color);
        font-weight: 700;
      }

      .expenses-page .day-switcher {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        flex-wrap: wrap;
      }

      .expenses-page .day-switcher-date {
        min-width: 150px;
        text-align: center;
        font-weight: 700;
      }

      .expenses-page .day-totals {
        display: inline-grid;
        grid-template-columns: repeat(4, minmax(145px, 1fr));
        gap: .5rem;
        align-items: stretch;
      }

      .expenses-page .day-total-item {
        border: 1px solid rgba(var(--bs-border-color-rgb), .55);
        border-radius: .5rem;
        padding: .55rem .85rem;
        background: rgba(var(--bs-body-bg-rgb), .35);
        text-align: center;
      }

      .expenses-page .day-total-item.total {
        border-color: rgba(234, 84, 85, .5);
        background: rgba(234, 84, 85, .09);
      }

      .expenses-page .day-total-label {
        color: var(--bs-secondary-color);
        font-size: .78rem;
        white-space: nowrap;
      }

      .expenses-page .day-total-value {
        direction: ltr;
        font-size: 1.05rem;
        font-weight: 700;
      }

      .expenses-page .day-total-value.danger {
        color: var(--bs-danger);
      }

      .expenses-page .quick-date-picker {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        flex-wrap: wrap;
        padding: .75rem;
        border: 1px solid rgba(var(--bs-border-color-rgb), .55);
        border-radius: .5rem;
        background: rgba(var(--bs-body-bg-rgb), .35);
      }

      .expenses-page .quick-date-input {
        width: 168px;
        direction: ltr;
        unicode-bidi: isolate;
        text-align: center;
        font-weight: 700;
      }

      .expenses-page .quick-date-meta {
        min-width: 115px;
        text-align: center;
        color: var(--bs-secondary-color);
        font-size: .82rem;
      }

      .expenses-page .month-navigator {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .65rem;
        background: rgba(var(--bs-body-bg-rgb), .42);
      }

      .expenses-page .month-title {
        font-weight: 800;
        font-size: 1.05rem;
      }

      .expenses-page .month-controls {
        display: flex;
        align-items: center;
        gap: .5rem;
        flex-wrap: wrap;
      }

      .expenses-page .month-input {
        width: 170px;
        direction: ltr;
        text-align: center;
        font-weight: 700;
      }

      .expenses-page .print-only {
        display: none;
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

        .expenses-page,
        .expenses-page * {
          visibility: visible;
        }

        .expenses-page {
          position: absolute;
          inset: 0;
          width: 100%;
          margin: 0 !important;
          background: #fff !important;
          color: #111 !important;
        }

        .expenses-page .no-print {
          display: none !important;
        }

        .expenses-page button {
          display: none !important;
        }

        .expenses-page .print-only {
          display: block !important;
        }

        .expenses-page .print-title {
          margin-bottom: 4mm;
          padding-bottom: 3mm;
          border-bottom: 1px solid #222;
          text-align: center;
        }

        .expenses-page .print-title h4 {
          margin: 0 0 1mm !important;
          font-size: 11pt;
          font-weight: 800;
        }

        .expenses-page .print-title div {
          color: #555 !important;
          font-size: 6.8pt;
        }

        .expenses-page .card {
          border: 0 !important;
          box-shadow: none !important;
          margin-bottom: 4mm !important;
          background: transparent !important;
          page-break-inside: auto;
        }

        .expenses-page .card-header,
        .expenses-page .card-body {
          padding: 0 0 3mm !important;
        }

        .expenses-page .card-header {
          display: block !important;
          margin-bottom: 2mm;
          border-bottom: 1px solid #d8dde3 !important;
        }

        .expenses-page h5 {
          font-size: 8.5pt !important;
          font-weight: 800;
        }

        .expenses-page .row {
          --bs-gutter-x: 2mm;
          --bs-gutter-y: 2mm;
        }

        .expenses-page .summary-box,
        .expenses-page .day-total-item {
          border: 1px solid #c8ced6 !important;
          border-radius: 2mm;
          padding: 2mm 2.5mm !important;
          background: #f7f9fb !important;
        }

        .expenses-page .summary-value,
        .expenses-page .day-total-value {
          font-size: 7.2pt !important;
          color: #111 !important;
        }

        .expenses-page .table-responsive {
          overflow: visible !important;
        }

        .expenses-page .table {
          width: 100% !important;
          min-width: 0 !important;
          border-collapse: collapse !important;
          table-layout: fixed;
          font-size: 5.7pt;
        }

        .expenses-page .sheet-table th {
          background: #edf1f5 !important;
          color: #000 !important;
          border: 1px solid #9fa8b3 !important;
          padding: .65mm .35mm !important;
          font-size: 4.8pt !important;
          font-weight: 700;
          white-space: normal;
          line-height: 1.05;
        }

        .expenses-page .sheet-table td {
          border: 1px solid #c8ced6 !important;
          padding: .8mm .5mm !important;
          background: #fff !important;
          color: #111 !important;
          vertical-align: middle;
          word-break: break-word;
        }

        .expenses-page .amount-cell,
        .expenses-page .date-cell {
          font-size: 5.6pt;
          white-space: nowrap;
        }

        .expenses-page .badge {
          border: 1px solid #b5bdc7;
          background: #f3f5f7 !important;
          color: #111 !important;
          font-size: 5.5pt;
        }

        .expenses-page .text-danger,
        .expenses-page .text-muted {
          color: #111 !important;
        }
      }

      @media (max-width: 767.98px) {
        .expenses-page {
          margin-inline: -.75rem;
        }

        .expenses-page .card {
          border-radius: 0;
        }

        .expenses-page .card-header {
          align-items: stretch !important;
          gap: .75rem;
        }

        .expenses-page .card-header > div,
        .expenses-page .card-header > span {
          width: 100%;
        }

        .expenses-page .table {
          min-width: 920px;
        }

        .expenses-page .table-responsive {
          position: relative;
          scrollbar-width: thin;
          scrollbar-color: rgba(var(--bs-danger-rgb), .65) rgba(var(--bs-border-color-rgb), .25);
        }

        .expenses-page .table-responsive::-webkit-scrollbar {
          height: 8px;
        }

        .expenses-page .table-responsive::-webkit-scrollbar-thumb {
          background: rgba(var(--bs-danger-rgb), .65);
          border-radius: 999px;
        }

        .expenses-page .table-responsive::-webkit-scrollbar-track {
          background: rgba(var(--bs-border-color-rgb), .25);
        }

        .expenses-page .mobile-scroll-hint {
          display: flex;
          align-items: center;
          justify-content: center;
          gap: .35rem;
          padding: .5rem .75rem;
          color: var(--bs-secondary-color);
          font-size: .78rem;
          background: rgba(var(--bs-body-bg-rgb), .55);
          border-bottom: 1px solid rgba(var(--bs-border-color-rgb), .35);
        }

        .expenses-page .sheet-table th:first-child,
        .expenses-page .sheet-table td:first-child {
          position: sticky;
          right: 0;
          z-index: 2;
          background: var(--bs-card-bg);
          box-shadow: -6px 0 10px rgba(0, 0, 0, .08);
        }

        .expenses-page .sheet-table th:first-child {
          z-index: 3;
          background: #7b1f0c;
        }

        .expenses-page .summary-box {
          text-align: center;
        }

        .expenses-page .day-switcher {
          display: grid;
          grid-template-columns: 1fr;
          width: 100%;
          padding-inline: .75rem;
        }

        .expenses-page .quick-date-picker {
          display: grid;
          grid-template-columns: 44px minmax(0, 1fr) 44px;
          gap: .5rem;
          width: 100%;
          padding: .65rem;
          border-radius: .5rem;
        }

        .expenses-page .quick-date-picker .btn-icon {
          width: 44px;
          height: 44px;
        }

        .expenses-page .quick-date-input {
          width: 100%;
          min-width: 0;
          height: 44px;
          font-size: 1rem;
        }

        .expenses-page .quick-date-picker .btn-label-secondary,
        .expenses-page .quick-date-meta {
          grid-column: 1 / -1;
          width: 100%;
        }

        .expenses-page .quick-date-meta {
          padding-top: .15rem;
          font-size: .78rem;
        }

        .expenses-page .month-navigator {
          align-items: stretch;
          flex-direction: column;
        }

        .expenses-page .month-controls,
        .expenses-page .month-input,
        .expenses-page .month-controls .btn-label-secondary {
          width: 100%;
        }

        .expenses-page .month-controls .btn-icon {
          flex: 1 1 44px;
        }

        .expenses-page .day-totals {
          grid-column: 1 / -1;
          display: grid;
          grid-template-columns: 1fr;
          width: 100%;
        }

        .expenses-page .day-total-item {
          display: flex;
          align-items: center;
          justify-content: space-between;
          gap: 1rem;
        }

        .expenses-page .card-body.border-top.text-center {
          padding-inline: .75rem;
        }
      }

      @media print {
        .expenses-page .card-body.border-top.text-center {
          padding: 0 0 3mm !important;
          text-align: initial !important;
        }

        .expenses-page .day-switcher,
        .expenses-page .quick-date-picker {
          display: block !important;
          width: 100% !important;
          padding: 0 !important;
          border: 0 !important;
          background: transparent !important;
        }

        .expenses-page .day-switcher-date {
          display: block !important;
          width: 100% !important;
          min-width: 0 !important;
          margin-bottom: 2mm;
          padding: 1.5mm 2mm;
          border: 1px solid #c8ced6;
          border-radius: 1.5mm;
          text-align: center;
          font-size: 8pt;
          font-weight: 800;
          background: #f7f9fb !important;
        }

        .expenses-page .day-totals {
          display: grid !important;
          grid-template-columns: repeat(2, 1fr) !important;
          gap: 1.3mm !important;
          width: 100% !important;
        }

        .expenses-page .day-total-item {
          display: block !important;
          min-height: 0 !important;
          text-align: center !important;
        }

        .expenses-page .day-total-label {
          font-size: 5.8pt !important;
          white-space: normal !important;
        }

        .expenses-page .card-body.border-top > .summary-box {
          max-width: 70mm;
          margin-inline: auto;
          text-align: center !important;
        }

        .expenses-page .summary-box {
          min-height: 0 !important;
        }
      }
    </style>
  @endsection

  <div class="no-print">
    @include('_partials/_alerts/alert-general')
  </div>

  <div class="expenses-page">
    <div class="print-only print-title">
      <h4>تقرير المصاريف - {{ $accountName }}</h4>
      <div>يوم <span class="date-text">{{ \Carbon\Carbon::parse($selectedDate)->format('d-m-Y') }}</span></div>
    </div>

    <div class="alert alert-info mb-4 no-print">
      {{ __('accounts.business_day_locked_notice') }}
      <strong class="date-text">{{ $this->getCurrentBusinessDate() }}</strong>
    </div>

    <div class="month-navigator mb-4 no-print">
      <div>
        <div class="month-title">مصروفات شهر {{ \Carbon\Carbon::parse($selectedMonth.'-01')->translatedFormat('F Y') }}</div>
        <small class="text-muted">اختر الشهر المطلوب، ويمكنك الرجوع للشهور السابقة بسهولة.</small>
      </div>
      <div class="month-controls">
        <button wire:click="showPreviousMonth" type="button" class="btn btn-label-danger btn-icon" title="الشهر السابق" @disabled(! $this->canShowPreviousMonth())>
          <i class="ti ti-chevron-right"></i>
        </button>
        <select wire:model.live="selectedMonth" class="form-select month-input">
          @foreach($availableMonths as $month)
            <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
          @endforeach
        </select>
        <button wire:click="showNextMonth" type="button" class="btn btn-label-danger btn-icon" title="الشهر التالي" @disabled(! $this->canShowNextMonth())>
          <i class="ti ti-chevron-left"></i>
        </button>
        <button wire:click="showCurrentMonth" type="button" class="btn btn-label-secondary">
          الشهر الحالي
        </button>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-0">ملخص مصاريف اليوم المحدد</h5>
          <small class="text-muted date-text">{{ \Carbon\Carbon::parse($selectedDate)->format('d-m-Y') }}</small>
        </div>
        <div class="d-flex align-items-center gap-2 no-print">
          <button onclick="window.print()" type="button" class="btn btn-sm btn-label-secondary">
            <i class="ti ti-printer me-1"></i>
            طباعة اليوم
          </button>
          <span class="badge bg-label-danger">{{ $selectedDayExpenses->count() }} {{ __('accounts.records') }}</span>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-lg-3 col-md-6">
            <div class="summary-box today-summary">
              <div class="text-muted small mb-1">{{ __('accounts.purchases') }}</div>
              <div class="summary-value">AED {{ number_format($dailySummary['purchase'], 2) }}</div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="summary-box today-summary">
              <div class="text-muted small mb-1">{{ __('accounts.cash_withdrawal') }}</div>
              <div class="summary-value">AED {{ number_format($dailySummary['cash_withdrawal'], 2) }}</div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="summary-box today-summary">
              <div class="text-muted small mb-1">{{ __('accounts.tips') }}</div>
              <div class="summary-value">AED {{ number_format($dailySummary['tip'], 2) }}</div>
            </div>
          </div>
          <div class="col-lg-3 col-md-6">
            <div class="summary-box today-summary">
              <div class="text-muted small mb-1">{{ __('accounts.total_expenses') }}</div>
              <div class="summary-value text-danger">AED {{ number_format($dailySummary['total'], 2) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    @if($this->canCreateExpense())
    <div class="card mb-4 no-print">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-0">{{ __('accounts.expenses') }} {{ $accountName }}</h5>
          <small class="text-muted">{{ $this->isPerfumesAccount() ? __('accounts.perfume_expenses_hint') : __('accounts.expenses_hint_auto_time') }}</small>
        </div>
        <span class="badge bg-label-danger">{{ $expenses->count() }} {{ __('accounts.records') }}</span>
      </div>

      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-lg-2 col-md-4">
            <label class="form-label">{{ __('accounts.expense_kind') }}</label>
            <select wire:model.live="expenseForm.expense_kind" class="form-select @error('expenseForm.expense_kind') is-invalid @enderror">
              <option value="purchase">{{ __('accounts.purchases') }}</option>
              <option value="cash_withdrawal">{{ __('accounts.cash_withdrawal') }}</option>
              <option value="tip">{{ __('accounts.tip') }}</option>
              @if($this->isMaktoomAccount())
                <option value="advance">سلفة موظف</option>
              @endif
            </select>
            @error('expenseForm.expense_kind')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          @if($this->usesWithdrawnTo())
            <div class="col-lg-3 col-md-4">
              <label class="form-label">{{ in_array($expenseForm['expense_kind'], ['tip', 'advance'], true) ? __('accounts.employee_name') : __('accounts.withdrawn_to') }}</label>
              @if($expenseForm['expense_kind'] === 'tip')
                <select wire:model.defer="expenseForm.withdrawn_to" class="form-select @error('expenseForm.withdrawn_to') is-invalid @enderror">
                  <option value="">{{ __('accounts.employee_name') }}</option>
                  @foreach($employees as $employee)
                    @php($employeeName = $employee->full_name ?: $employee->first_name)
                    <option value="{{ $employeeName }}">{{ $employeeName }}</option>
                  @endforeach
                </select>
              @elseif($expenseForm['expense_kind'] === 'advance')
                <select wire:model.live="expenseForm.employee_id" class="form-select @error('expenseForm.employee_id') is-invalid @enderror">
                  <option value="">{{ __('accounts.employee_name') }}</option>
                  @foreach($employees as $employee)
                    @php($employeeName = $employee->full_name ?: $employee->first_name)
                    <option value="{{ $employee->id }}">{{ $employeeName }}</option>
                  @endforeach
                </select>
              @else
                <input wire:model.defer="expenseForm.withdrawn_to" type="text" class="form-control @error('expenseForm.withdrawn_to') is-invalid @enderror">
              @endif
              @error('expenseForm.withdrawn_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
              @error('expenseForm.employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-lg-2 col-md-4">
              <label class="form-label">{{ $expenseForm['expense_kind'] === 'tip' ? __('accounts.tip_amount') : ($expenseForm['expense_kind'] === 'advance' ? 'مبلغ السلفة' : __('accounts.amount_for_withdrawal')) }}</label>
              <input wire:model.defer="expenseForm.unit_price" type="number" min="0.01" step="0.01" class="form-control amount-cell @error('expenseForm.unit_price') is-invalid @enderror">
              @error('expenseForm.unit_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @if($expenseForm['expense_kind'] === 'advance' && $advanceEmployeeSalaryInfo)
              <div class="col-lg-3 col-md-4">
                <div class="summary-box h-100">
                  <div class="small text-muted mb-1">المتبقي من الراتب</div>
                  <div class="summary-value text-success">AED {{ number_format((float) $advanceEmployeeSalaryInfo['remaining'], 2) }}</div>
                  <div class="small text-muted mt-1">
                    الراتب: AED {{ number_format((float) $advanceEmployeeSalaryInfo['gross'], 2) }}
                    <span class="mx-1">|</span>
                    الخصومات والسلف: AED {{ number_format((float) $advanceEmployeeSalaryInfo['discounts'], 2) }}
                  </div>
                </div>
              </div>
            @endif
          @else
            <div class="col-lg-2 col-md-4">
              <label class="form-label">{{ $this->isPerfumesAccount() ? __('accounts.perfume_purchase_name') : __('accounts.purchase_name') }}</label>
              <input wire:model.defer="expenseForm.service" type="text" class="form-control @error('expenseForm.service') is-invalid @enderror">
              @error('expenseForm.service')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-lg-2 col-md-4">
              <label class="form-label">{{ __('accounts.has_invoice') }}</label>
              <select wire:model.live="expenseForm.has_invoice" class="form-select @error('expenseForm.has_invoice') is-invalid @enderror">
                <option value="1">{{ __('accounts.invoice_yes') }}</option>
                <option value="0">{{ __('accounts.invoice_no') }}</option>
              </select>
              @error('expenseForm.has_invoice')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @if((string) $expenseForm['has_invoice'] === '1')
              <div class="col-lg-4 col-md-6">
                <div class="invoice-upload-panel">
                  <div class="invoice-upload-heading">
                    <span class="invoice-upload-icon" aria-hidden="true"><i class="ti ti-receipt-2"></i></span>
                    <div class="invoice-upload-copy">
                      <label for="expense-invoice-image-create" class="form-label fw-semibold mb-1">رفع صورة الفاتورة</label>
                      <div class="small text-muted">JPG أو PNG أو WEBP، بحد أقصى 4 ميغابايت.</div>
                    </div>
                    <label for="expense-invoice-image-create" class="btn btn-sm btn-label-danger mb-0 text-nowrap">
                      <i class="ti ti-photo-plus me-1"></i>اختيار صورة
                    </label>
                    <input id="expense-invoice-image-create" wire:model="invoiceImage" type="file" accept="image/jpeg,image/png,image/webp" class="visually-hidden">
                  </div>
                  @error('invoiceImage')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                  <div wire:loading wire:target="invoiceImage" class="small text-muted mt-2">
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    جارٍ رفع الصورة...
                  </div>
                  @if($invoiceImage && str_starts_with($invoiceImage->getMimeType(), 'image/'))
                    <div class="invoice-upload-preview">
                      <img src="{{ $invoiceImage->temporaryUrl() }}" alt="معاينة الفاتورة المختارة">
                      <div class="invoice-upload-preview-copy">
                        <div class="fw-semibold">تم اختيار الفاتورة</div>
                        <small class="text-muted">{{ $invoiceImage->getClientOriginalName() }}</small>
                      </div>
                    </div>
                  @endif
                </div>
              </div>
            @endif

            <div class="col-lg-2 col-md-4">
              <label class="form-label">{{ __('accounts.price') }}</label>
              <input wire:model.defer="expenseForm.unit_price" type="number" min="0.01" step="0.01" class="form-control amount-cell @error('expenseForm.unit_price') is-invalid @enderror">
              @error('expenseForm.unit_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-lg-2 col-md-4">
              <label class="form-label">{{ __('accounts.qty') }}</label>
              <input wire:model.defer="expenseForm.quantity" type="number" min="1" step="1" inputmode="numeric" class="form-control amount-cell @error('expenseForm.quantity') is-invalid @enderror">
              @error('expenseForm.quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          @endif

          @if($this->canCreateBackdatedExpense())
            <div class="col-lg-2 col-md-4">
              <label class="form-label">{{ __('accounts.date') }}</label>
              <input
                wire:model.live="expenseForm.date"
                type="date"
                dir="ltr"
                lang="en"
                max="{{ $this->getCurrentBusinessDate() }}"
                class="form-control @error('expenseForm.date') is-invalid @enderror"
              >
              @error('expenseForm.date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          @endif

          <div class="col-lg-10 col-md-8">
            <label class="form-label">{{ __('accounts.purchase_note') }}</label>
            <input wire:model.defer="expenseForm.note" type="text" class="form-control @error('expenseForm.note') is-invalid @enderror">
            @error('expenseForm.note')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-lg-2 col-md-4">
            <button wire:click="createExpense" type="button" class="btn btn-danger w-100" @disabled(! $this->isBusinessWindowOpen() && ! $this->canCreateBackdatedExpense())>
              <i class="ti ti-plus me-1"></i>
              {{ __('accounts.add_expense') }}
            </button>
          </div>
        </div>
      </div>
    </div>
    @endif

    <div class="card">
      <div class="mobile-scroll-hint no-print">
        <i class="ti ti-arrows-horizontal"></i>
        حرّك الجدول يمين ويسار لعرض باقي البيانات
      </div>
      <div class="table-responsive">
        <table class="table sheet-table align-middle mb-0">
          <thead>
            <tr>
              <th class="text-center">{{ __('accounts.date') }}</th>
              <th class="text-center">{{ __('accounts.time') }}</th>
              <th class="text-center">{{ __('accounts.expense_kind') }}</th>
              <th class="text-center">{{ $this->isPerfumesAccount() ? __('accounts.perfume_expense_name') : __('accounts.purchase_name') }} / {{ __('accounts.withdrawn_to') }}</th>
              <th class="text-center">{{ __('accounts.has_invoice') }}</th>
              <th class="text-center">{{ __('accounts.qty') }}</th>
              <th class="text-center">{{ __('accounts.price') }}</th>
              <th class="text-center">{{ __('accounts.total') }}</th>
              <th class="text-center">{{ __('accounts.purchase_note') }}</th>
              <th class="text-center no-print">{{ __('accounts.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr class="table-section-row">
              <td colspan="10">
                مصاريف يوم <span class="date-text">{{ \Carbon\Carbon::parse($selectedDate)->format('d-m-Y') }}</span>
                @if($selectedDate === $this->getCurrentBusinessDate())
                  <span class="badge bg-label-success ms-2">اليوم الحالي</span>
                @endif
              </td>
            </tr>

            @foreach($selectedDayExpenses as $expense)
              @php($kind = $expense->expense_kind ?: 'purchase')
              <tr class="today-row">
                <td class="date-cell">{{ \Carbon\Carbon::parse($expense->date)->format('d-m-Y') }}</td>
                <td class="text-center">{{ $expense->created_at?->timezone('Asia/Dubai')->format('h:i A') }}</td>
                <td class="text-center">
                  {{ $kind === 'advance' ? 'سلفة موظف' : ($kind === 'tip' ? __('accounts.tip') : ($kind === 'cash_withdrawal' ? __('accounts.cash_withdrawal') : __('accounts.purchases'))) }}
                </td>
                <td class="text-center">{{ in_array($kind, ['cash_withdrawal', 'tip', 'advance'], true) ? ($expense->withdrawn_to ?: $expense->employee?->full_name ?: '---') : ($expense->service ?: '---') }}</td>
                <td class="text-center">
                  @if($kind === 'purchase')
                    <div class="invoice-cell">
                      <span>{{ $expense->has_invoice ? __('accounts.invoice_yes') : __('accounts.invoice_no') }}</span>
                      @if($expense->has_invoice && $this->invoiceImageUrl($expense))
                        <button
                          type="button"
                          class="invoice-image-trigger"
                          data-bs-toggle="modal"
                          data-bs-target="#expenseInvoicePreviewModal"
                          data-image-url="{{ $this->invoiceImageUrl($expense) }}"
                          aria-label="معاينة صورة الفاتورة"
                          title="اضغط لمعاينة الفاتورة"
                        >
                          <img src="{{ $this->invoiceImageUrl($expense) }}" alt="صورة الفاتورة" class="invoice-image" loading="lazy">
                        </button>
                      @endif
                    </div>
                  @else
                    ---
                  @endif
                </td>
                <td class="amount-cell">{{ $kind === 'purchase' ? number_format((float) ($expense->quantity ?: 1), 0) : '---' }}</td>
                <td class="amount-cell">AED {{ number_format((float) ($expense->unit_price ?: $expense->amount), 2) }}</td>
                <td class="amount-cell">AED {{ number_format((float) $expense->amount, 2) }}</td>
                <td>{{ $expense->note ?: '---' }}</td>
                <td class="text-center actions-cell no-print">
                  @if($this->canModifyExpense($expense) || $this->canDeleteExpense($expense))
                    <div class="actions-wrap">
                      @if($this->canModifyExpense($expense))
                        <button
                          wire:click="showEditExpenseModal({{ $expense->id }})"
                          type="button"
                          class="btn btn-sm btn-icon btn-label-info"
                          data-bs-toggle="modal"
                          data-bs-target="#expenseEditModal"
                          title="{{ __('accounts.edit') }}"
                        >
                          <i class="ti ti-pencil"></i>
                        </button>
                      @endif
                      @if($this->canDeleteExpense($expense))
                        <button wire:click="confirmDeleteExpense({{ $expense->id }})" type="button" class="btn btn-sm btn-icon btn-label-danger" title="{{ __('accounts.delete') }}">
                          <i class="ti ti-trash"></i>
                        </button>
                      @endif
                      @if($confirmedExpenseId === $expense->id)
                        <button wire:click="deleteExpense" type="button" class="btn btn-xs btn-danger">
                          {{ __('accounts.sure') }}
                        </button>
                      @endif
                    </div>
                  @else
                    <span class="badge bg-label-secondary">{{ __('accounts.record_locked') }}</span>
                  @endif
                </td>
              </tr>
            @endforeach

            @if($selectedDayExpenses->isEmpty())
              <tr>
                <td colspan="10" class="text-center text-muted py-5">
                  لا توجد مصاريف في هذا اليوم.
                </td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>

      <div class="card-body border-top text-center">
        <div class="day-switcher">
          <div class="quick-date-picker no-print">
            <button wire:click="showPreviousDay" type="button" class="btn btn-label-danger btn-icon" title="اليوم السابق">
              <i class="ti ti-chevron-right"></i>
            </button>
            <input
              wire:model.live="selectedDate"
              type="date"
              dir="ltr"
              lang="en"
              max="{{ $this->getCurrentBusinessDate() }}"
              class="form-control quick-date-input"
            >
            <button wire:click="showNextDay" type="button" class="btn btn-label-danger btn-icon" title="اليوم التالي" @disabled(! $this->canShowNextDay())>
              <i class="ti ti-chevron-left"></i>
            </button>
            <button wire:click="showCurrentBusinessDay" type="button" class="btn btn-label-secondary">
              اليوم الحالي
            </button>
            <div class="quick-date-meta">
              {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l') }}
              @if($selectedDate === $this->getCurrentBusinessDate())
                <div class="text-success small">اليوم الحالي</div>
              @endif
            </div>
          </div>
          <div class="day-switcher-date print-only">
            <span class="date-text">{{ \Carbon\Carbon::parse($selectedDate)->format('d-m-Y') }}</span>
          </div>
          <div class="day-totals">
            <div class="day-total-item">
              <div class="day-total-label">{{ __('accounts.purchases') }}</div>
              <div class="day-total-value">AED {{ number_format($dailySummary['purchase'], 2) }}</div>
            </div>
            <div class="day-total-item">
              <div class="day-total-label">{{ __('accounts.cash_withdrawal') }}</div>
              <div class="day-total-value">AED {{ number_format($dailySummary['cash_withdrawal'], 2) }}</div>
            </div>
            <div class="day-total-item">
              <div class="day-total-label">{{ __('accounts.tips') }}</div>
              <div class="day-total-value">AED {{ number_format($dailySummary['tip'], 2) }}</div>
            </div>
            @if($this->isMaktoomAccount())
              <div class="day-total-item">
                <div class="day-total-label">السلف</div>
                <div class="day-total-value">AED {{ number_format($dailySummary['advance'], 2) }}</div>
              </div>
            @endif
            <div class="day-total-item total">
              <div class="day-total-label">الإجمالي</div>
              <div class="day-total-value danger">AED {{ number_format($dailySummary['total'], 2) }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="card-body border-top">
        <div class="row g-3">
          <div class="col-lg-4 col-md-6">
            <div class="summary-box h-100">
              <div class="text-muted small mb-1">{{ __('accounts.total_expenses') }} - {{ $selectedMonth }}</div>
              <div class="amount-cell fw-bold fs-5 text-danger">
                AED {{ number_format($monthlySummary['total'], 2) }}
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="summary-box h-100">
              <div class="text-muted small mb-1">{{ __('accounts.cash') }} - {{ $selectedMonth }}</div>
              <div class="amount-cell fw-bold fs-5 text-success">
                AED {{ number_format($monthlySummary['cash'], 2) }}
              </div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="summary-box h-100">
              <div class="text-muted small mb-1">{{ __('accounts.cash_treasury_balance') }} - {{ $selectedMonth }}</div>
              <div class="amount-cell fw-bold fs-5 {{ $monthlySummary['treasury'] >= 0 ? 'text-success' : 'text-danger' }}">
                AED {{ number_format($monthlySummary['treasury'], 2) }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div wire:ignore.self class="modal fade" id="expenseEditModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ __('accounts.edit_expense') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">{{ __('accounts.expense_kind') }}</label>
                <select wire:model.live="expenseForm.expense_kind" class="form-select @error('expenseForm.expense_kind') is-invalid @enderror">
                  <option value="purchase">{{ __('accounts.purchases') }}</option>
                  <option value="cash_withdrawal">{{ __('accounts.cash_withdrawal') }}</option>
                  <option value="tip">{{ __('accounts.tip') }}</option>
                  @if($this->isMaktoomAccount())
                    <option value="advance">سلفة موظف</option>
                  @endif
                </select>
                @error('expenseForm.expense_kind')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              @if($this->usesWithdrawnTo())
                <div class="col-md-6">
                  <label class="form-label">{{ in_array($expenseForm['expense_kind'], ['tip', 'advance'], true) ? __('accounts.employee_name') : __('accounts.withdrawn_to') }}</label>
                  @if($expenseForm['expense_kind'] === 'tip')
                    <select wire:model.defer="expenseForm.withdrawn_to" class="form-select @error('expenseForm.withdrawn_to') is-invalid @enderror">
                      <option value="">{{ __('accounts.employee_name') }}</option>
                      @foreach($employees as $employee)
                        @php($employeeName = $employee->full_name ?: $employee->first_name)
                        <option value="{{ $employeeName }}">{{ $employeeName }}</option>
                      @endforeach
                    </select>
                  @elseif($expenseForm['expense_kind'] === 'advance')
                    <select wire:model.defer="expenseForm.employee_id" class="form-select @error('expenseForm.employee_id') is-invalid @enderror">
                      <option value="">{{ __('accounts.employee_name') }}</option>
                      @foreach($employees as $employee)
                        @php($employeeName = $employee->full_name ?: $employee->first_name)
                        <option value="{{ $employee->id }}">{{ $employeeName }}</option>
                      @endforeach
                    </select>
                  @else
                    <input wire:model.defer="expenseForm.withdrawn_to" type="text" class="form-control @error('expenseForm.withdrawn_to') is-invalid @enderror">
                  @endif
                  @error('expenseForm.withdrawn_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  @error('expenseForm.employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ $expenseForm['expense_kind'] === 'tip' ? __('accounts.tip_amount') : ($expenseForm['expense_kind'] === 'advance' ? 'مبلغ السلفة' : __('accounts.amount_for_withdrawal')) }}</label>
                  <input wire:model.defer="expenseForm.unit_price" type="number" min="0.01" step="0.01" class="form-control amount-cell @error('expenseForm.unit_price') is-invalid @enderror">
                  @error('expenseForm.unit_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              @else
                <div class="col-md-6">
                  <label class="form-label">{{ $this->isPerfumesAccount() ? __('accounts.perfume_purchase_name') : __('accounts.purchase_name') }}</label>
                  <input wire:model.defer="expenseForm.service" type="text" class="form-control @error('expenseForm.service') is-invalid @enderror">
                  @error('expenseForm.service')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ __('accounts.has_invoice') }}</label>
                  <select wire:model.live="expenseForm.has_invoice" class="form-select @error('expenseForm.has_invoice') is-invalid @enderror">
                    <option value="1">{{ __('accounts.invoice_yes') }}</option>
                    <option value="0">{{ __('accounts.invoice_no') }}</option>
                  </select>
                  @error('expenseForm.has_invoice')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                @if((string) $expenseForm['has_invoice'] === '1')
                  <div class="col-md-6">
                    <div class="invoice-upload-panel">
                      <div class="invoice-upload-heading">
                        <span class="invoice-upload-icon" aria-hidden="true"><i class="ti ti-receipt-2"></i></span>
                        <div class="invoice-upload-copy">
                          <label for="expense-invoice-image-edit" class="form-label fw-semibold mb-1">رفع صورة الفاتورة</label>
                          <div class="small text-muted">JPG أو PNG أو WEBP، بحد أقصى 4 ميغابايت.</div>
                        </div>
                        <label for="expense-invoice-image-edit" class="btn btn-sm btn-label-danger mb-0 text-nowrap">
                          <i class="ti ti-photo-plus me-1"></i>اختيار صورة
                        </label>
                        <input id="expense-invoice-image-edit" wire:model="invoiceImage" type="file" accept="image/jpeg,image/png,image/webp" class="visually-hidden">
                      </div>
                      @error('invoiceImage')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                      <div wire:loading wire:target="invoiceImage" class="small text-muted mt-2">
                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        جارٍ رفع الصورة...
                      </div>
                      @if($invoiceImage && str_starts_with($invoiceImage->getMimeType(), 'image/'))
                        <div class="invoice-upload-preview">
                          <img src="{{ $invoiceImage->temporaryUrl() }}" alt="معاينة الفاتورة المختارة">
                          <div class="invoice-upload-preview-copy">
                            <div class="fw-semibold">تم اختيار صورة جديدة</div>
                            <small class="text-muted">{{ $invoiceImage->getClientOriginalName() }}</small>
                          </div>
                        </div>
                      @elseif($this->editingInvoiceImageUrl())
                        <div class="invoice-upload-preview">
                          <img src="{{ $this->editingInvoiceImageUrl() }}" alt="صورة الفاتورة الحالية">
                          <div class="invoice-upload-preview-copy">
                            <div class="fw-semibold">صورة الفاتورة الحالية</div>
                            <a href="{{ $this->editingInvoiceImageUrl() }}" target="_blank" rel="noopener" class="small">عرض الصورة</a>
                          </div>
                        </div>
                      @endif
                    </div>
                  </div>
                @endif
                <div class="col-md-6">
                  <label class="form-label">{{ __('accounts.price') }}</label>
                  <input wire:model.defer="expenseForm.unit_price" type="number" min="0.01" step="0.01" class="form-control amount-cell @error('expenseForm.unit_price') is-invalid @enderror">
                  @error('expenseForm.unit_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ __('accounts.qty') }}</label>
                  <input wire:model.defer="expenseForm.quantity" type="number" min="1" step="1" inputmode="numeric" class="form-control amount-cell @error('expenseForm.quantity') is-invalid @enderror">
                  @error('expenseForm.quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              @endif

              @if($this->canEditExpenseDateTime())
                <div class="col-md-6">
                  <label class="form-label">{{ __('accounts.date') }}</label>
                  <input wire:model.defer="expenseForm.date" type="text" dir="ltr" lang="en" inputmode="numeric" placeholder="2026-05-13" class="form-control date-text @error('expenseForm.date') is-invalid @enderror">
                  @error('expenseForm.date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ __('accounts.time') }}</label>
                  <input wire:model.defer="expenseForm.time" type="text" inputmode="numeric" placeholder="21:14" class="form-control @error('expenseForm.time') is-invalid @enderror">
                  @error('expenseForm.time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              @endif

              <div class="col-12">
                <label class="form-label">{{ __('accounts.purchase_note') }}</label>
                <textarea wire:model.defer="expenseForm.note" class="form-control @error('expenseForm.note') is-invalid @enderror"></textarea>
                @error('expenseForm.note')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('accounts.cancel') }}</button>
            <button wire:click="updateExpense" type="button" class="btn btn-primary">{{ __('accounts.save_changes') }}</button>
          </div>
        </div>
      </div>
    </div>

    <div wire:ignore.self class="modal fade" id="expenseInvoicePreviewModal" tabindex="-1" aria-labelledby="expenseInvoicePreviewTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered invoice-preview-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="expenseInvoicePreviewTitle">صورة الفاتورة</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
          </div>
          <div class="modal-body">
            <div class="invoice-preview-frame">
              <img id="expenseInvoicePreviewImage" src="" alt="صورة الفاتورة">
            </div>
            <div class="invoice-preview-actions">
              <a id="expenseInvoiceOpenLink" href="#" target="_blank" rel="noopener" class="btn btn-label-info">
                <i class="ti ti-external-link me-1"></i>عرض
              </a>
              <a id="expenseInvoiceDownloadLink" href="#" download class="btn btn-label-success">
                <i class="ti ti-download me-1"></i>تحميل
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @section('page-script')
    <script>
      const expenseInvoicePreviewModal = document.getElementById('expenseInvoicePreviewModal');

      if (expenseInvoicePreviewModal) {
        expenseInvoicePreviewModal.addEventListener('show.bs.modal', event => {
          const imageUrl = event.relatedTarget?.dataset.imageUrl;

          if (!imageUrl) {
            return;
          }

          document.getElementById('expenseInvoicePreviewImage').src = imageUrl;
          document.getElementById('expenseInvoiceOpenLink').href = imageUrl;
          document.getElementById('expenseInvoiceDownloadLink').href = imageUrl;
        });

        expenseInvoicePreviewModal.addEventListener('hidden.bs.modal', () => {
          document.getElementById('expenseInvoicePreviewImage').removeAttribute('src');
        });
      }
    </script>
  @endsection
</div>
