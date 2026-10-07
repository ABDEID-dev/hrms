<div dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
  @section('title', __('accounts.revenues').' '.$accountName)

  @section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
  @endsection

  @section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
  @endsection

  @section('page-style')
    <style>
      .revenues-page .sheet-table th {
        background: #264b12;
        color: #fff;
        vertical-align: middle;
        white-space: nowrap;
      }

      .revenues-page .amount-cell {
        direction: ltr;
        text-align: center;
        white-space: nowrap;
      }

      .revenues-page .date-cell {
        direction: ltr;
        unicode-bidi: isolate;
        text-align: center;
        white-space: nowrap;
        min-width: 112px;
      }

      .revenues-page input[type="date"],
      .revenues-page .date-text {
        direction: ltr;
        unicode-bidi: isolate;
        text-align: center;
      }

      .revenues-page .date-text {
        display: inline-block;
      }

      .revenues-page .inventory-product-select,
      .revenues-page .inventory-product-select + .select2-container,
      .revenues-page .inventory-product-select + .select2-container .select2-selection,
      .revenues-page .inventory-product-select + .select2-container .select2-selection__rendered,
      .revenue-product-option,
      .select2-search--dropdown .select2-search__field {
        direction: ltr;
        text-align: left;
        unicode-bidi: isolate;
      }

      .revenues-page .inventory-product-select + .select2-container {
        width: 100% !important;
      }

      .revenue-product-option {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
      }

      .revenue-product-option .product-name {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      .revenue-product-option .product-price {
        flex: 0 0 auto;
        font-weight: 700;
        color: var(--bs-success);
        white-space: nowrap;
      }

      .revenues-page .note-preview {
        max-width: 230px;
        border: 0;
        padding: 0;
        color: inherit;
        background: transparent;
        text-align: inherit;
        line-height: 1.6;
        white-space: normal;
      }

      .revenues-page .note-preview.has-more {
        color: var(--bs-primary);
        text-decoration: underline;
        text-underline-offset: .2rem;
        cursor: pointer;
      }

      .revenues-page .summary-box {
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .5rem;
        padding: .85rem 1rem;
        background: rgba(var(--bs-body-bg-rgb), .45);
      }

      .revenues-page .summary-value {
        direction: ltr;
        font-weight: 700;
        font-size: 1.15rem;
      }

      .revenues-page .compact-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
      }

      .revenues-page .compact-summary-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 148px;
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .5rem;
        padding: 1rem;
        background: rgba(var(--bs-body-bg-rgb), .45);
      }

      .revenues-page .compact-summary-label {
        color: var(--bs-secondary-color);
        font-size: .82rem;
      }

      .revenues-page .compact-summary-value {
        direction: ltr;
        margin-top: .35rem;
        font-size: 1.25rem;
        font-weight: 800;
      }

      .revenues-page .compact-lines {
        display: grid;
        gap: .45rem;
        margin-top: .85rem;
        padding-top: .75rem;
        border-top: 1px solid rgba(var(--bs-border-color-rgb), .32);
      }

      .revenues-page .compact-line {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        color: var(--bs-secondary-color);
        font-size: .8rem;
      }

      .revenues-page .compact-line strong {
        direction: ltr;
        color: var(--bs-heading-color);
        white-space: nowrap;
      }

      .revenues-page .day-modal-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
      }

      .revenues-page .day-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        width: 100%;
      }

      .revenues-page .day-modal-title {
        min-width: 0;
      }

      .revenues-page .day-modal-nav {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: .5rem;
        flex-wrap: wrap;
      }

      .revenues-page .day-modal-date {
        min-width: 120px;
        padding: .45rem .75rem;
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .5rem;
        background: rgba(var(--bs-body-bg-rgb), .35);
        direction: ltr;
        unicode-bidi: isolate;
        text-align: center;
        font-weight: 700;
      }

      .revenues-page .day-modal-cards {
        display: none;
      }

      .revenues-page .treasury-days-cards {
        display: none;
      }

      .revenues-page .treasury-day-card {
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .5rem;
        padding: .9rem;
        background: rgba(var(--bs-body-bg-rgb), .35);
      }

      .revenues-page .treasury-day-card + .treasury-day-card {
        margin-top: .75rem;
      }

      .revenues-page .day-revenue-card {
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .5rem;
        padding: .9rem;
        background: rgba(var(--bs-body-bg-rgb), .35);
      }

      .revenues-page .day-revenue-card + .day-revenue-card {
        margin-top: .75rem;
      }

      .revenues-page .day-revenue-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .75rem;
      }

      .revenues-page .day-revenue-amount {
        direction: ltr;
        color: var(--bs-success);
        font-size: 1.05rem;
        font-weight: 800;
        white-space: nowrap;
      }

      .revenues-page .day-revenue-lines {
        display: grid;
        gap: .5rem;
      }

      .revenues-page .day-revenue-line {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .75rem;
        padding-top: .5rem;
        border-top: 1px solid rgba(var(--bs-border-color-rgb), .25);
      }

      .revenues-page .day-revenue-line span {
        color: var(--bs-secondary-color);
        font-size: .78rem;
      }

      .revenues-page .day-revenue-line strong {
        min-width: 0;
        text-align: end;
        font-weight: 700;
        overflow-wrap: anywhere;
      }

      .revenues-page .expense-breakdown {
        margin-top: .6rem;
        padding-top: .55rem;
        border-top: 1px solid rgba(var(--bs-border-color-rgb), .32);
        display: grid;
        gap: .35rem;
      }

      .revenues-page .expense-breakdown-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        color: var(--bs-secondary-color);
        font-size: .78rem;
      }

      .revenues-page .expense-breakdown-row strong {
        direction: ltr;
        color: var(--bs-heading-color);
        white-space: nowrap;
      }

      .revenues-page .day-switcher {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        flex-wrap: wrap;
      }

      .revenues-page .day-switcher-date {
        min-width: 150px;
        text-align: center;
        font-weight: 700;
      }

      .revenues-page .day-totals {
        display: inline-grid;
        grid-template-columns: repeat(6, minmax(120px, 1fr));
        gap: .5rem;
        align-items: stretch;
      }

      .revenues-page .day-total-item {
        border: 1px solid rgba(var(--bs-border-color-rgb), .55);
        border-radius: .5rem;
        padding: .55rem .85rem;
        background: rgba(var(--bs-body-bg-rgb), .35);
        text-align: center;
      }

      .revenues-page .day-total-item.total {
        border-color: rgba(40, 199, 111, .5);
        background: rgba(40, 199, 111, .09);
      }

      .revenues-page .day-total-label {
        color: var(--bs-secondary-color);
        font-size: .78rem;
        white-space: nowrap;
      }

      .revenues-page .day-total-value {
        direction: ltr;
        font-size: 1.05rem;
        font-weight: 700;
      }

      .revenues-page .day-total-value.success {
        color: var(--bs-success);
      }

      .revenues-page .today-summary {
        border-color: rgba(40, 199, 111, .45);
        background: rgba(40, 199, 111, .08);
      }

      .revenues-page .today-row > td {
        background: rgba(40, 199, 111, .08);
      }

      .revenues-page .table-section-row td {
        background: rgba(var(--bs-primary-rgb), .12);
        color: var(--bs-heading-color);
        font-weight: 700;
      }

      .revenues-page .table-section-row.previous td {
        background: rgba(var(--bs-border-color-rgb), .22);
      }

      .revenues-page .actions-cell {
        min-width: 110px;
      }

      .revenues-page .actions-wrap {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .35rem;
      }

      .revenues-page .mobile-scroll-hint {
        display: none;
      }

      .revenues-page .quick-date-picker {
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

      .revenues-page .quick-date-input {
        width: 168px;
        direction: ltr;
        unicode-bidi: isolate;
        text-align: center;
        font-weight: 700;
      }

      .revenues-page .quick-date-meta {
        min-width: 115px;
        text-align: center;
        color: var(--bs-secondary-color);
        font-size: .82rem;
      }

      .revenues-page .month-navigator {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .65rem;
        background: rgba(var(--bs-body-bg-rgb), .42);
      }

      .revenues-page .month-title {
        font-weight: 800;
        font-size: 1.05rem;
      }

      .revenues-page .mobile-revenue-toggle {
        display: none;
      }

      .revenues-page .mobile-revenue-toggle .ti-chevron-down {
        transition: transform .18s ease;
      }

      .revenues-page .mobile-revenue-toggle [aria-expanded="true"] .ti-chevron-down {
        transform: rotate(180deg);
      }

      .revenues-page .revenue-form-card.collapse {
        display: block;
      }

      .revenues-page .desktop-reports-toggle {
        display: flex;
        justify-content: flex-end;
      }

      .revenues-page .desktop-reports-toggle .ti-chevron-down {
        transition: transform .18s ease;
      }

      .revenues-page .desktop-reports-toggle [aria-expanded="true"] .ti-chevron-down {
        transform: rotate(180deg);
      }

      .revenues-page .month-controls {
        display: flex;
        align-items: center;
        gap: .5rem;
        flex-wrap: wrap;
      }

      .revenues-page .month-input {
        width: 170px;
        direction: ltr;
        text-align: center;
        font-weight: 700;
      }

      .revenues-page .print-only {
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

        .revenues-page,
        .revenues-page * {
          visibility: visible;
        }

        .revenues-page {
          position: absolute;
          inset: 0;
          width: 100%;
          margin: 0 !important;
          background: #fff !important;
          color: #111 !important;
        }

        .revenues-page .no-print {
          display: none !important;
        }

        .revenues-page button {
          display: none !important;
        }

        .revenues-page .print-only {
          display: block !important;
        }

        .revenues-page .print-title {
          margin-bottom: 4mm;
          padding-bottom: 3mm;
          border-bottom: 1px solid #222;
          text-align: center;
        }

        .revenues-page .print-title h4 {
          margin: 0 0 1mm !important;
          font-size: 11pt;
          font-weight: 800;
        }

        .revenues-page .print-title div {
          color: #555 !important;
          font-size: 6.8pt;
        }

        .revenues-page .card {
          border: 0 !important;
          box-shadow: none !important;
          margin-bottom: 4mm !important;
          background: transparent !important;
          page-break-inside: auto;
        }

        .revenues-page .card-header,
        .revenues-page .card-body {
          padding: 0 0 3mm !important;
        }

        .revenues-page .card-header {
          display: block !important;
          margin-bottom: 2mm;
          border-bottom: 1px solid #d8dde3 !important;
        }

        .revenues-page h5 {
          font-size: 8.5pt !important;
          font-weight: 800;
        }

        .revenues-page .row {
          --bs-gutter-x: 2mm;
          --bs-gutter-y: 2mm;
        }

        .revenues-page .summary-box,
        .revenues-page .day-total-item {
          border: 1px solid #c8ced6 !important;
          border-radius: 2mm;
          padding: 2mm 2.5mm !important;
          background: #f7f9fb !important;
        }

        .revenues-page .summary-value,
        .revenues-page .day-total-value {
          font-size: 7.2pt !important;
          color: #111 !important;
        }

        .revenues-page .table-responsive {
          overflow: visible !important;
        }

        .revenues-page .table {
          width: 100% !important;
          min-width: 0 !important;
          border-collapse: collapse !important;
          table-layout: fixed;
          font-size: 5.7pt;
        }

        .revenues-page .sheet-table th {
          background: #edf1f5 !important;
          color: #000 !important;
          border: 1px solid #9fa8b3 !important;
          padding: .65mm .35mm !important;
          font-size: 4.8pt !important;
          font-weight: 700;
          white-space: normal;
          line-height: 1.05;
        }

        .revenues-page .sheet-table td {
          border: 1px solid #c8ced6 !important;
          padding: .8mm .5mm !important;
          background: #fff !important;
          color: #111 !important;
          vertical-align: middle;
          word-break: break-word;
        }

        .revenues-page .amount-cell,
        .revenues-page .date-cell {
          font-size: 5.6pt;
          white-space: nowrap;
        }

        .revenues-page .badge {
          border: 1px solid #b5bdc7;
          background: #f3f5f7 !important;
          color: #111 !important;
          font-size: 5.5pt;
        }

        .revenues-page .text-success,
        .revenues-page .text-danger,
        .revenues-page .text-muted {
          color: #111 !important;
        }
      }

      @media (max-width: 767.98px) {
        .revenues-page {
          margin-inline: -.75rem;
          padding-top: 2.25rem;
        }

        .revenues-page .mobile-revenue-toggle {
          display: block;
          padding-inline: .75rem;
        }

        .revenues-page .mobile-revenue-toggle .btn {
          display: flex;
          align-items: center;
          justify-content: center;
          gap: .45rem;
          width: 100%;
          min-height: 46px;
          font-weight: 700;
        }

        .revenues-page .revenue-form-card.collapse:not(.show) {
          display: none;
        }

        .revenues-page .revenue-form-card .card-header {
          padding: 1rem .9rem;
        }

        .revenues-page .revenue-form-card .card-body {
          padding: 1rem .9rem;
        }

        .revenues-page .desktop-reports-toggle {
          display: flex;
          padding-inline: .75rem;
        }

        .revenues-page .desktop-reports-toggle .btn {
          width: 100%;
          min-height: 44px;
          font-weight: 700;
        }

        .revenues-page .card {
          border-radius: 0;
        }

        .revenues-page .card-header {
          align-items: stretch !important;
          gap: .75rem;
        }

        .revenues-page .card-header > div,
        .revenues-page .card-header > span {
          width: 100%;
        }

        .revenues-page .table {
          min-width: 860px;
        }

        .revenues-page .table-responsive {
          position: relative;
          scrollbar-width: thin;
          scrollbar-color: rgba(var(--bs-primary-rgb), .65) rgba(var(--bs-border-color-rgb), .25);
        }

        .revenues-page .table-responsive::-webkit-scrollbar {
          height: 8px;
        }

        .revenues-page .table-responsive::-webkit-scrollbar-thumb {
          background: rgba(var(--bs-primary-rgb), .65);
          border-radius: 999px;
        }

        .revenues-page .table-responsive::-webkit-scrollbar-track {
          background: rgba(var(--bs-border-color-rgb), .25);
        }

        .revenues-page .mobile-scroll-hint {
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

        .revenues-page .sheet-table th:first-child,
        .revenues-page .sheet-table td:first-child {
          position: sticky;
          right: 0;
          z-index: 2;
          background: var(--bs-card-bg);
          box-shadow: -6px 0 10px rgba(0, 0, 0, .08);
        }

        .revenues-page .sheet-table th:first-child {
          z-index: 3;
          background: #264b12;
        }

        .revenues-page .summary-box {
          text-align: center;
        }

        .revenues-page .summary-value {
          font-size: 1.05rem;
        }

        .revenues-page .day-switcher {
          display: grid;
          grid-template-columns: 1fr;
          width: 100%;
          padding-inline: .75rem;
        }

        .revenues-page .quick-date-picker {
          display: grid;
          grid-template-columns: 44px minmax(0, 1fr) 44px;
          gap: .5rem;
          width: 100%;
          padding: .65rem;
          border-radius: .5rem;
        }

        .revenues-page .quick-date-picker .btn-icon {
          width: 44px;
          height: 44px;
        }

        .revenues-page .quick-date-input {
          width: 100%;
          min-width: 0;
          height: 44px;
          font-size: 1rem;
        }

        .revenues-page .quick-date-picker .btn-label-secondary,
        .revenues-page .quick-date-picker .btn-primary,
        .revenues-page .quick-date-meta {
          grid-column: 1 / -1;
          width: 100%;
        }

        .revenues-page .quick-date-meta {
          padding-top: .15rem;
          font-size: .78rem;
        }

        .revenues-page .month-navigator {
          align-items: stretch;
          flex-direction: column;
          gap: .65rem;
          padding: .75rem;
        }

        .revenues-page .month-title {
          font-size: .95rem;
        }

        .revenues-page .month-navigator small {
          display: none;
        }

        .revenues-page .month-controls {
          display: grid;
          grid-template-columns: 44px minmax(0, 1fr) 44px;
          gap: .5rem;
          width: 100%;
        }

        .revenues-page .month-input {
          width: 100%;
          min-width: 0;
          height: 44px;
        }

        .revenues-page .month-controls .btn-icon {
          width: 44px;
          height: 44px;
        }

        .revenues-page .month-controls .btn-label-secondary {
          grid-column: 1 / -1;
          width: 100%;
          height: 40px;
        }

        .revenues-page .compact-summary,
        .revenues-page .day-modal-grid {
          grid-template-columns: 1fr;
        }

        .revenues-page .compact-summary-card {
          min-height: 0;
        }

        .revenues-page .day-modal-header {
          align-items: stretch;
          flex-direction: column;
        }

        .revenues-page .day-modal-nav {
          display: grid;
          grid-template-columns: 44px minmax(0, 1fr) 44px;
          width: 100%;
        }

        .revenues-page .day-modal-nav .btn-icon {
          width: 44px;
          height: 44px;
        }

        .revenues-page .day-modal-nav .btn-label-secondary {
          grid-column: 1 / -1;
          width: 100%;
        }

        .revenues-page .day-modal-date {
          min-width: 0;
          height: 44px;
        }

        .revenues-page .day-modal-table {
          display: none;
        }

        .revenues-page .day-modal-cards {
          display: block;
        }

        .revenues-page .treasury-days-table {
          display: none;
        }

        .revenues-page .treasury-days-cards {
          display: block;
        }

        .revenues-page .modal-dialog-scrollable .modal-body {
          padding-inline: .85rem;
        }

        .revenues-page .day-totals {
          grid-column: 1 / -1;
          display: grid;
          grid-template-columns: 1fr;
          width: 100%;
        }

        .revenues-page .day-total-item {
          display: flex;
          align-items: center;
          justify-content: space-between;
          gap: 1rem;
        }

        .revenues-page .card-body.border-top.text-center {
          padding-inline: .75rem;
        }
      }

      @media print {
        .revenues-page .desktop-reports-toggle {
          display: none !important;
        }

        .revenues-page .desktop-reports-collapse.collapse {
          display: block !important;
        }

        .revenues-page .card-body.border-top.text-center {
          padding: 0 0 3mm !important;
          text-align: initial !important;
        }

        .revenues-page .day-switcher,
        .revenues-page .quick-date-picker {
          display: block !important;
          width: 100% !important;
          padding: 0 !important;
          border: 0 !important;
          background: transparent !important;
        }

        .revenues-page .day-switcher-date {
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

        .revenues-page .day-totals {
          display: grid !important;
          grid-template-columns: repeat(3, 1fr) !important;
          gap: 1.3mm !important;
          width: 100% !important;
        }

        .revenues-page .day-total-item {
          display: block !important;
          min-height: 0 !important;
          text-align: center !important;
        }

        .revenues-page .day-total-label {
          font-size: 5.8pt !important;
          white-space: normal !important;
        }

        .revenues-page .card-body.border-top > .row {
          display: grid !important;
          grid-template-columns: repeat(2, 1fr) !important;
          gap: 1.3mm !important;
        }

        .revenues-page .card-body.border-top > .row > [class*="col-"] {
          width: auto !important;
          max-width: none !important;
          padding: 0 !important;
        }

        .revenues-page .summary-box {
          min-height: 0 !important;
          text-align: center !important;
        }
      }
    </style>
  @endsection

  <div class="no-print">
    @include('_partials/_alerts/alert-general')
  </div>

  <div class="revenues-page">
    <div class="print-only print-title">
      <h4>تقرير الإيرادات - {{ $accountName }}</h4>
      <div>يوم <span class="date-text">{{ \Carbon\Carbon::parse($selectedDate)->format('d-m-Y') }}</span></div>
    </div>

    <div class="desktop-reports-toggle mb-3 no-print">
      <button
        class="btn btn-label-primary"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#desktopReportsCollapse"
        aria-expanded="false"
        aria-controls="desktopReportsCollapse"
      >
        <i class="ti ti-chart-bar me-1"></i>
        <span class="d-none d-md-inline">عرض التقارير</span>
        <span class="d-inline d-md-none">عرض تقارير اليوم والشهر</span>
        <i class="ti ti-chevron-down ms-1"></i>
      </button>
    </div>

    <div wire:ignore.self class="collapse desktop-reports-collapse" id="desktopReportsCollapse">
      <div class="month-navigator mb-4 no-print">
        <div>
          <div class="month-title">إيرادات شهر {{ \Carbon\Carbon::parse($selectedMonth.'-01')->translatedFormat('F Y') }}</div>
          <small class="text-muted">اختر الشهر المطلوب، ويمكنك الرجوع للشهور السابقة بسهولة.</small>
        </div>
        <div class="month-controls">
          <button wire:click="showPreviousMonth" type="button" class="btn btn-label-success btn-icon" title="الشهر السابق" @disabled(! $this->canShowPreviousMonth())>
            <i class="ti ti-chevron-right"></i>
          </button>
          <select wire:model.live="selectedMonth" class="form-select month-input">
            @foreach($availableMonths as $month)
              <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
            @endforeach
          </select>
          <button wire:click="showNextMonth" type="button" class="btn btn-label-success btn-icon" title="الشهر التالي" @disabled(! $this->canShowNextMonth())>
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
          <h5 class="mb-0">ملخص اليوم المحدد</h5>
          <small class="text-muted">
            {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l') }}
            <span class="date-text ms-1">{{ \Carbon\Carbon::parse($selectedDate)->format('d-m-Y') }}</span>
          </small>
        </div>
        <div class="d-flex align-items-center gap-2 no-print">
          <button onclick="window.print()" type="button" class="btn btn-sm btn-label-secondary">
            <i class="ti ti-printer me-1"></i>
            طباعة اليوم
          </button>
          <span class="badge bg-label-primary">{{ $selectedDayRevenues->count() }} {{ __('accounts.records') }}</span>
        </div>
      </div>
      <div class="card-body">
        <div class="quick-date-picker no-print mb-3">
          <button wire:click="showPreviousDay" type="button" class="btn btn-label-primary btn-icon" title="اليوم السابق">
            <i class="ti ti-chevron-right"></i>
          </button>
          <input
            wire:model.live="selectedDate"
            type="date"
            dir="ltr"
            lang="en"
            min="{{ $this->selectedMonthStartDate() }}"
            max="{{ $this->selectedMonthSelectableEndDate() }}"
            class="form-control quick-date-input"
          >
          <button wire:click="showNextDay" type="button" class="btn btn-label-primary btn-icon" title="اليوم التالي" @disabled(! $this->canShowNextDay())>
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
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#selectedDaySummaryModal">
            <i class="ti ti-eye me-1"></i>
            عرض تفاصيل اليوم
          </button>
        </div>

        <div class="compact-summary">
          <div class="compact-summary-card today-summary">
            <div>
              <div class="compact-summary-label">{{ __('accounts.revenues_before_expenses') }}</div>
              <div class="compact-summary-value text-success">AED {{ number_format($dailySummary['total'], 2) }}</div>
            </div>
            <div class="compact-lines">
              <div class="compact-line">
                <span>{{ __('accounts.cash') }}</span>
                <strong>AED {{ number_format($dailySummary['cash'], 2) }}</strong>
              </div>
              <div class="compact-line">
                <span>{{ __('accounts.visa') }}</span>
                <strong>AED {{ number_format($dailySummary['visa'], 2) }}</strong>
              </div>
            </div>
          </div>

          <div class="compact-summary-card today-summary">
            <div>
              <div class="compact-summary-label">{{ __('accounts.revenues_after_expenses') }}</div>
              <div class="compact-summary-value {{ $dailySummary['net_total'] >= 0 ? 'text-success' : 'text-danger' }}">
                AED {{ number_format($dailySummary['net_total'], 2) }}
              </div>
            </div>
            <div class="compact-lines">
              <div class="compact-line">
                <span>{{ __('accounts.revenues_before_expenses') }}</span>
                <strong>AED {{ number_format($dailySummary['total'], 2) }}</strong>
              </div>
              <div class="compact-line">
                <span>{{ __('accounts.total_expenses') }}</span>
                <strong>AED {{ number_format($dailySummary['expenses'], 2) }}</strong>
              </div>
            </div>
          </div>

          <div class="compact-summary-card today-summary">
            <div>
              <div class="compact-summary-label">{{ __('accounts.total_expenses') }}</div>
              <div class="compact-summary-value text-danger">AED {{ number_format($dailySummary['expenses'], 2) }}</div>
            </div>
            <div class="compact-lines">
              <div class="compact-line">
                <span>{{ __('accounts.purchases') }}</span>
                <strong>AED {{ number_format($dailySummary['purchases'], 2) }}</strong>
              </div>
              <div class="compact-line">
                <span>{{ __('accounts.cash_withdrawals') }}</span>
                <strong>AED {{ number_format($dailySummary['cash_withdrawals'], 2) }}</strong>
              </div>
              <div class="compact-line">
                <span>{{ __('accounts.tips') }}</span>
                <strong>AED {{ number_format($dailySummary['tips'], 2) }}</strong>
              </div>
              @if(($dailySummary['advances'] ?? 0) > 0)
                <div class="compact-line">
                  <span>السلف</span>
                  <strong>AED {{ number_format($dailySummary['advances'], 2) }}</strong>
                </div>
              @endif
            </div>
          </div>

          <div class="compact-summary-card today-summary">
            <div>
              <div class="compact-summary-label">{{ __('accounts.cash_treasury_balance') }}</div>
              <div class="compact-summary-value {{ $dailySummary['treasury'] >= 0 ? 'text-success' : 'text-danger' }}">
                AED {{ number_format($dailySummary['treasury'], 2) }}
              </div>
            </div>
            <div class="compact-lines">
              <div class="compact-line">
                <span>حتى اليوم المحدد</span>
                <strong>{{ \Carbon\Carbon::parse($selectedDate)->format('d-m-Y') }}</strong>
              </div>
              <div class="compact-line">
                <span>{{ __('accounts.records') }}</span>
                <strong>{{ $selectedDayRevenues->count() }}</strong>
              </div>
            </div>
          </div>
        </div>
      </div>
      </div>
    </div>

    @if($this->canCreateRevenue())
    <div class="mobile-revenue-toggle no-print mb-3">
      <button
        class="btn btn-success"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#revenueFormCollapse"
        aria-expanded="false"
        aria-controls="revenueFormCollapse"
      >
        <i class="ti ti-plus"></i>
        تسجيل إيراد جديد
        <i class="ti ti-chevron-down"></i>
      </button>
    </div>

    <div wire:ignore.self class="card mb-4 no-print revenue-form-card collapse" id="revenueFormCollapse">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-0">{{ __('accounts.revenues') }} {{ $accountName }}</h5>
          <small class="text-muted">{{ $this->isPerfumesAccount() ? __('accounts.perfume_revenues_hint') : __('accounts.revenues_hint') }}</small>
        </div>
        <span class="badge bg-label-success">{{ $revenues->count() }} {{ __('accounts.records') }}</span>
      </div>

      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-lg-2 col-md-4">
            <label class="form-label">نوع الإيراد</label>
            <select wire:model.live="revenueForm.revenue_kind" class="form-select @error('revenueForm.revenue_kind') is-invalid @enderror">
              @foreach($this->revenueKindOptions() as $kindValue => $kindLabel)
                <option value="{{ $kindValue }}">{{ $kindLabel }}</option>
              @endforeach
            </select>
            @error('revenueForm.revenue_kind')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          @if($this->productRevenue())
            <div class="col-lg-3 col-md-4">
              <label class="form-label">{{ __('accounts.product') }}</label>
              <div wire:ignore>
                <select id="revenueProductSelect" data-placeholder="{{ __('accounts.search_product') }}" class="form-select inventory-product-select @error('revenueForm.inventory_product_id') is-invalid @enderror">
                  <option value="">{{ __('accounts.search_product') }}</option>
                @foreach($inventoryProducts as $product)
                  <option
                    value="{{ $product->id }}"
                    data-name="{{ $this->productEnglishNameLabel($product) }}"
                    data-price="{{ $this->productPriceLabel($product) }}"
                  >
                    {{ $this->productOptionLabel($product) }}
                  </option>
                @endforeach
                </select>
              </div>
              @error('revenueForm.inventory_product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          @endif

          @unless($this->isPerfumesAccount() || $this->productRevenue())
            <div class="col-lg-2 col-md-4">
              <label class="form-label">{{ __('accounts.employee_name') }}</label>
              <select wire:model.defer="revenueForm.employee_name" class="form-select @error('revenueForm.employee_name') is-invalid @enderror">
                <option value="">{{ __('accounts.employee_name') }}</option>
                @foreach($employees as $employee)
                  @php($employeeName = $employee->full_name ?: $employee->first_name)
                  <option value="{{ $employeeName }}">{{ $employeeName }}</option>
                @endforeach
              </select>
              @error('revenueForm.employee_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          @endunless

          <div class="col-lg-2 col-md-4">
            <label class="form-label">{{ $this->productRevenue() ? 'اسم المنتج' : ($this->isPerfumesAccount() ? 'تركيبة عطر' : __('accounts.service')) }}</label>
            <input wire:model.defer="revenueForm.service" type="text" class="form-control @error('revenueForm.service') is-invalid @enderror">
            @error('revenueForm.service')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          @if($this->isPerfumesAccount() || $this->productRevenue())
            <div class="col-lg-2 col-md-4">
              <label class="form-label">{{ $this->productRevenue() ? 'الكمية' : __('accounts.pieces_count') }}</label>
              @php($selectedProductAvailableStock = $this->selectedProductAvailableStock())
              <input
                wire:model.defer="revenueForm.quantity"
                type="number"
                min="{{ $this->selectedProductUnit() === 'piece' || $this->isPerfumesAccount() ? '1' : '0.001' }}"
                @if($this->productRevenue() && $selectedProductAvailableStock !== null)
                  max="{{ $selectedProductAvailableStock }}"
                @endif
                step="{{ $this->selectedProductUnit() === 'piece' || $this->isPerfumesAccount() ? '1' : '0.001' }}"
                inputmode="{{ $this->selectedProductUnit() === 'piece' || $this->isPerfumesAccount() ? 'numeric' : 'decimal' }}"
                class="form-control amount-cell @error('revenueForm.quantity') is-invalid @enderror"
              >
              @if($this->productRevenue() && $selectedProductAvailableStock !== null)
                <div class="form-text text-muted">المتاح: {{ $this->productQuantityLabel($selectedProductAvailableStock, $inventoryProducts->firstWhere('id', (int) $revenueForm['inventory_product_id'])) }}</div>
              @endif
              @error('revenueForm.quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-lg-2 col-md-4">
              <label class="form-label">{{ __('accounts.unit_price') }}</label>
              <input wire:model.defer="revenueForm.unit_price" type="number" min="0.01" step="0.01" class="form-control amount-cell @error('revenueForm.unit_price') is-invalid @enderror">
              @error('revenueForm.unit_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          @else
            <div class="col-lg-2 col-md-4">
              <label class="form-label">{{ __('accounts.service_price') }}</label>
              <input wire:model.defer="revenueForm.amount" type="number" min="0.01" step="0.01" class="form-control amount-cell @error('revenueForm.amount') is-invalid @enderror">
              @error('revenueForm.amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          @endif

          <div class="col-lg-2 col-md-4">
            <label class="form-label">{{ __('accounts.cash_visa') }}</label>
            <select wire:model.defer="revenueForm.payment_method" class="form-select @error('revenueForm.payment_method') is-invalid @enderror">
              <option value="cash">{{ __('accounts.cash') }}</option>
              <option value="visa">{{ __('accounts.visa') }}</option>
            </select>
            @error('revenueForm.payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          @if($this->canCreateBackdatedRevenue())
            <div class="col-lg-2 col-md-4">
              <label class="form-label">{{ __('accounts.date') }}</label>
              <input
                wire:model.defer="revenueForm.date"
                type="date"
                dir="ltr"
                lang="en"
                max="{{ $this->getCurrentBusinessDate() }}"
                class="form-control @error('revenueForm.date') is-invalid @enderror"
              >
              @error('revenueForm.date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          @endif

          <div class="col-lg-2 col-md-4">
            <label class="form-label">{{ __('accounts.customer_name') }}</label>
            <input wire:model.defer="revenueForm.customer_name" type="text" class="form-control @error('revenueForm.customer_name') is-invalid @enderror">
            @error('revenueForm.customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-lg-10 col-md-8">
            <label class="form-label">{{ __('accounts.note') }}</label>
            <input wire:model.defer="revenueForm.note" type="text" class="form-control @error('revenueForm.note') is-invalid @enderror">
            @error('revenueForm.note')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-lg-2 col-md-4">
            <button wire:click="createRevenue" type="button" class="btn btn-success w-100" @disabled(! $this->isBusinessWindowOpen() && ! $this->canCreateBackdatedRevenue())>
              <i class="ti ti-plus me-1"></i>
              <span class="d-none d-md-inline">{{ __('accounts.add_revenue') }}</span>
              <span class="d-inline d-md-none">حفظ الإيراد</span>
            </button>
          </div>
        </div>
      </div>
    </div>
    @endif

    <div class="card">
      <div class="mobile-scroll-hint no-print d-none">
        <i class="ti ti-arrows-horizontal"></i>
        حرّك الجدول يمين ويسار لعرض باقي البيانات
      </div>
      <div class="table-responsive d-none">
        <table class="table sheet-table align-middle mb-0">
          <thead>
            <tr>
              <th class="text-center">{{ __('accounts.date') }}</th>
              <th class="text-center">{{ __('accounts.time') }}</th>
              <th class="text-center">النوع</th>
              @unless($this->isPerfumesAccount())
                <th class="text-center">{{ __('accounts.employee_name') }}</th>
              @endunless
              <th class="text-center">{{ $this->isPerfumesAccount() ? __('accounts.perfume_name') : __('accounts.service') }}</th>
              @if($this->isPerfumesAccount())
                <th class="text-center">{{ __('accounts.pieces_count') }}</th>
                <th class="text-center">{{ __('accounts.unit_price') }}</th>
                <th class="text-center">{{ __('accounts.total') }}</th>
              @else
                <th class="text-center">الكمية</th>
                <th class="text-center">{{ __('accounts.unit_price') }}</th>
                <th class="text-center">{{ __('accounts.service_price') }}</th>
              @endif
              <th class="text-center">{{ __('accounts.cash_visa') }}</th>
              <th class="text-center">{{ __('accounts.note') }}</th>
              <th class="text-center">{{ __('accounts.customer_name') }}</th>
              <th class="text-center no-print">{{ __('accounts.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr class="table-section-row">
              <td colspan="{{ $this->isPerfumesAccount() ? 11 : 12 }}">
                إدخالات يوم <span class="date-text">{{ \Carbon\Carbon::parse($selectedDate)->format('d-m-Y') }}</span>
                @if($selectedDate === $this->getCurrentBusinessDate())
                  <span class="badge bg-label-success ms-2">اليوم الحالي</span>
                @endif
              </td>
            </tr>

            @foreach($selectedDayRevenues as $revenue)
              <tr class="today-row">
                <td class="date-cell">{{ \Carbon\Carbon::parse($revenue->date)->format('d-m-Y') }}</td>
                <td class="text-center">{{ $revenue->created_at?->timezone('Asia/Dubai')->format('h:i A') }}</td>
                <td class="text-center">
                  <span class="badge bg-label-{{ ($revenue->revenue_kind ?: 'service') === 'product' ? 'success' : 'primary' }}">
                    {{ ($revenue->revenue_kind ?: 'service') === 'product' ? 'منتج' : 'خدمة' }}
                  </span>
                </td>
                @unless($this->isPerfumesAccount())
                  <td class="text-center">{{ $revenue->employee_name }}</td>
                @endunless
                <td class="text-center">
                  {{ (($revenue->revenue_kind ?: 'service') === 'product' && $revenue->inventoryProduct) ? $this->productEnglishNameLabel($revenue->inventoryProduct) : $revenue->service }}
                </td>
                @if($this->isPerfumesAccount())
                  <td class="amount-cell">{{ number_format((float) ($revenue->quantity ?: 1), 0) }} قطعة</td>
                  <td class="amount-cell">AED {{ number_format((float) ($revenue->unit_price ?: $revenue->amount), 2) }}</td>
                  <td class="amount-cell">AED {{ number_format((float) $revenue->amount, 2) }}</td>
                @else
                  <td class="amount-cell">{{ (($revenue->revenue_kind ?: 'service') === 'product' && $revenue->inventoryProduct) ? $this->productQuantityLabel($revenue->quantity ?: 1, $revenue->inventoryProduct) : '---' }}</td>
                  <td class="amount-cell">{{ ($revenue->revenue_kind ?: 'service') === 'product' ? 'AED '.number_format((float) ($revenue->unit_price ?: 0), 2) : '---' }}</td>
                  <td class="amount-cell">AED {{ number_format((float) $revenue->amount, 2) }}</td>
                @endif
                <td class="text-center">{{ $revenue->payment_method === 'cash' ? __('accounts.cash') : __('accounts.visa') }}</td>
                <td>
                  @if($revenue->note)
                    @if(\Illuminate\Support\Str::length($revenue->note) > 20)
                      <button wire:click="showRevenueNote({{ $revenue->id }})" type="button" class="note-preview has-more">
                        {{ \Illuminate\Support\Str::limit($revenue->note, 20, '...') }}
                      </button>
                    @else
                      <span class="note-preview">{{ $revenue->note }}</span>
                    @endif
                  @else
                    ---
                  @endif
                </td>
                <td class="text-center">{{ $revenue->customer_name ?: '---' }}</td>
                <td class="text-center actions-cell no-print">
                  @if($this->canModifyRevenue($revenue) || $this->canDeleteRevenueRecord($revenue))
                    <div class="actions-wrap">
                      @if($this->canModifyRevenue($revenue))
                        <button
                          wire:click="showEditRevenueModal({{ $revenue->id }})"
                          type="button"
                          class="btn btn-sm btn-icon btn-label-info"
                          data-bs-toggle="modal"
                          data-bs-target="#revenueEditModal"
                          title="{{ __('accounts.edit') }}"
                        >
                          <i class="ti ti-pencil"></i>
                        </button>
                      @endif
                      @if($this->canDeleteRevenueRecord($revenue))
                        <button wire:click="confirmDeleteRevenue({{ $revenue->id }})" type="button" class="btn btn-sm btn-icon btn-label-danger" title="{{ __('accounts.delete') }}">
                          <i class="ti ti-trash"></i>
                        </button>
                      @endif
                      @if($confirmedRevenueId === $revenue->id)
                        <button wire:click="deleteRevenue" type="button" class="btn btn-xs btn-danger">
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

            @if($selectedDayRevenues->isEmpty())
              <tr>
                <td colspan="{{ $this->isPerfumesAccount() ? 11 : 12 }}" class="text-center text-muted py-5">
                  لا توجد إدخالات في هذا اليوم.
                </td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>

      <div class="card-body border-top">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
          <div class="fw-semibold">ملخص شهر {{ \Carbon\Carbon::parse($selectedMonth.'-01')->format('Y-m') }}</div>
          <span class="badge bg-label-primary">{{ $revenues->count() }} {{ __('accounts.records') }}</span>
        </div>
        <div class="compact-summary">
          <div class="compact-summary-card">
            <div>
              <div class="compact-summary-label">{{ __('accounts.revenues_before_expenses') }}</div>
              <div class="compact-summary-value">AED {{ number_format($summary['total'], 2) }}</div>
            </div>
            <div class="compact-lines">
              <div class="compact-line">
                <span>{{ __('accounts.cash') }}</span>
                <strong>AED {{ number_format($summary['cash'], 2) }}</strong>
              </div>
              <div class="compact-line">
                <span>{{ __('accounts.visa') }}</span>
                <strong>AED {{ number_format($summary['visa'], 2) }}</strong>
              </div>
            </div>
          </div>

          <div class="compact-summary-card">
            <div>
              <div class="compact-summary-label">{{ __('accounts.revenues_after_expenses') }}</div>
              <div class="compact-summary-value {{ $summary['net_total'] >= 0 ? 'text-success' : 'text-danger' }}">
                AED {{ number_format($summary['net_total'], 2) }}
              </div>
            </div>
            <div class="compact-lines">
              <div class="compact-line">
                <span>{{ __('accounts.revenues_before_expenses') }}</span>
                <strong>AED {{ number_format($summary['total'], 2) }}</strong>
              </div>
              <div class="compact-line">
                <span>{{ __('accounts.total_expenses') }}</span>
                <strong>AED {{ number_format($summary['expenses'], 2) }}</strong>
              </div>
            </div>
          </div>

          <div class="compact-summary-card">
            <div>
              <div class="compact-summary-label">{{ __('accounts.total_expenses') }}</div>
              <div class="compact-summary-value text-danger">AED {{ number_format($summary['expenses'], 2) }}</div>
            </div>
            <div class="compact-lines">
              <div class="compact-line">
                <span>{{ __('accounts.purchases') }}</span>
                <strong>AED {{ number_format($summary['purchases'], 2) }}</strong>
              </div>
              <div class="compact-line">
                <span>{{ __('accounts.cash_withdrawals') }}</span>
                <strong>AED {{ number_format($summary['cash_withdrawals'], 2) }}</strong>
              </div>
              <div class="compact-line">
                <span>{{ __('accounts.tips') }}</span>
                <strong>AED {{ number_format($summary['tips'], 2) }}</strong>
              </div>
              @if(($summary['advances'] ?? 0) > 0)
                <div class="compact-line">
                  <span>السلف</span>
                  <strong>AED {{ number_format($summary['advances'], 2) }}</strong>
                </div>
              @endif
            </div>
          </div>

          <div class="compact-summary-card">
            <div>
              <div class="compact-summary-label">{{ __('accounts.cash_treasury_balance') }} - كاش بعد المصاريف</div>
              <div class="compact-summary-value {{ $summary['treasury'] >= 0 ? 'text-success' : 'text-danger' }}">
                AED {{ number_format($summary['treasury'], 2) }}
              </div>
            </div>
            <div class="compact-lines">
              <div class="compact-line">
                <span>رصيد الشهر حتى</span>
                <strong>{{ \Carbon\Carbon::parse($this->selectedMonthSelectableEndDate())->format('d-m-Y') }}</strong>
              </div>
            </div>
            <button type="button" class="btn btn-sm btn-label-primary mt-3" data-bs-toggle="modal" data-bs-target="#monthlyTreasuryDaysModal">
              <i class="ti ti-list-details me-1"></i>
              عرض رصيد الأيام
            </button>
          </div>
        </div>
      </div>
    </div>

    <div wire:ignore.self class="modal fade" id="selectedDaySummaryModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <div class="day-modal-header">
              <div class="day-modal-title">
                <h5 class="modal-title">تقرير اليوم المحدد</h5>
                <small class="text-muted">{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l') }}</small>
              </div>
              <div class="day-modal-nav no-print">
                <button wire:click="showPreviousDay" type="button" class="btn btn-label-primary btn-icon" title="اليوم السابق">
                  <i class="ti ti-chevron-right"></i>
                </button>
                <div class="day-modal-date">{{ \Carbon\Carbon::parse($selectedDate)->format('d-m-Y') }}</div>
                <button wire:click="showNextDay" type="button" class="btn btn-label-primary btn-icon" title="اليوم التالي" @disabled(! $this->canShowNextDay())>
                  <i class="ti ti-chevron-left"></i>
                </button>
                <button wire:click="showCurrentBusinessDay" type="button" class="btn btn-label-secondary">
                  اليوم الحالي
                </button>
              </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="day-modal-grid mb-4">
              <div class="compact-summary-card">
                <div>
                  <div class="compact-summary-label">{{ __('accounts.revenues_before_expenses') }}</div>
                  <div class="compact-summary-value text-success">AED {{ number_format($dailySummary['total'], 2) }}</div>
                </div>
                <div class="compact-lines">
                  <div class="compact-line">
                    <span>{{ __('accounts.cash') }}</span>
                    <strong>AED {{ number_format($dailySummary['cash'], 2) }}</strong>
                  </div>
                  <div class="compact-line">
                    <span>{{ __('accounts.visa') }}</span>
                    <strong>AED {{ number_format($dailySummary['visa'], 2) }}</strong>
                  </div>
                </div>
              </div>

              <div class="compact-summary-card">
                <div>
                  <div class="compact-summary-label">{{ __('accounts.revenues_after_expenses') }}</div>
                  <div class="compact-summary-value {{ $dailySummary['net_total'] >= 0 ? 'text-success' : 'text-danger' }}">
                    AED {{ number_format($dailySummary['net_total'], 2) }}
                  </div>
                </div>
                <div class="compact-lines">
                  <div class="compact-line">
                    <span>{{ __('accounts.total_expenses') }}</span>
                    <strong>AED {{ number_format($dailySummary['expenses'], 2) }}</strong>
                  </div>
                  <div class="compact-line">
                    <span>{{ __('accounts.cash_treasury_balance') }}</span>
                    <strong>AED {{ number_format($dailySummary['treasury'], 2) }}</strong>
                  </div>
                </div>
              </div>

              <div class="compact-summary-card">
                <div>
                  <div class="compact-summary-label">{{ __('accounts.total_expenses') }}</div>
                  <div class="compact-summary-value text-danger">AED {{ number_format($dailySummary['expenses'], 2) }}</div>
                </div>
                <div class="compact-lines">
                  <div class="compact-line">
                    <span>{{ __('accounts.purchases') }}</span>
                    <strong>AED {{ number_format($dailySummary['purchases'], 2) }}</strong>
                  </div>
                  <div class="compact-line">
                    <span>{{ __('accounts.cash_withdrawals') }}</span>
                    <strong>AED {{ number_format($dailySummary['cash_withdrawals'], 2) }}</strong>
                  </div>
                  <div class="compact-line">
                    <span>{{ __('accounts.tips') }}</span>
                    <strong>AED {{ number_format($dailySummary['tips'], 2) }}</strong>
                  </div>
                </div>
              </div>

              <div class="compact-summary-card">
                <div>
                  <div class="compact-summary-label">{{ __('accounts.records') }}</div>
                  <div class="compact-summary-value">{{ $selectedDayRevenues->count() }}</div>
                </div>
                <div class="compact-lines">
                  <div class="compact-line">
                    <span>{{ __('accounts.cash_treasury_balance') }}</span>
                    <strong>AED {{ number_format($dailySummary['treasury'], 2) }}</strong>
                  </div>
                </div>
              </div>
            </div>

            <div class="day-modal-cards">
              @forelse($selectedDayRevenues as $revenue)
                <div class="day-revenue-card">
                  <div class="day-revenue-header">
                    <div>
                      <span class="badge bg-label-{{ ($revenue->revenue_kind ?: 'service') === 'product' ? 'success' : 'primary' }}">
                        {{ ($revenue->revenue_kind ?: 'service') === 'product' ? 'منتج' : 'خدمة' }}
                      </span>
                      <div class="text-muted small mt-2">{{ $revenue->created_at?->timezone('Asia/Dubai')->format('h:i A') }}</div>
                    </div>
                    <div class="day-revenue-amount">AED {{ number_format((float) $revenue->amount, 2) }}</div>
                  </div>
                  <div class="day-revenue-lines">
                    @unless($this->isPerfumesAccount())
                      <div class="day-revenue-line">
                        <span>{{ __('accounts.employee_name') }}</span>
                        <strong>{{ $revenue->employee_name ?: '---' }}</strong>
                      </div>
                    @endunless
                    <div class="day-revenue-line">
                      <span>{{ $this->isPerfumesAccount() ? __('accounts.perfume_name') : __('accounts.service') }}</span>
                      <strong>{{ (($revenue->revenue_kind ?: 'service') === 'product' && $revenue->inventoryProduct) ? $this->productEnglishNameLabel($revenue->inventoryProduct) : ($revenue->service ?: '---') }}</strong>
                    </div>
                    <div class="day-revenue-line">
                      <span>{{ __('accounts.cash_visa') }}</span>
                      <strong>{{ $revenue->payment_method === 'cash' ? __('accounts.cash') : __('accounts.visa') }}</strong>
                    </div>
                    <div class="day-revenue-line">
                      <span>{{ __('accounts.note') }}</span>
                      <strong>{{ $revenue->note ?: '---' }}</strong>
                    </div>
                    <div class="day-revenue-line">
                      <span>{{ __('accounts.customer_name') }}</span>
                      <strong>{{ $revenue->customer_name ?: '---' }}</strong>
                    </div>
                    @if($this->canModifyRevenue($revenue) || $this->canDeleteRevenueRecord($revenue))
                      <div class="day-revenue-line no-print">
                        <span>{{ __('accounts.actions') }}</span>
                        <strong>
                          <span class="actions-wrap">
                            @if($this->canModifyRevenue($revenue))
                              <button
                                wire:click="showEditRevenueModal({{ $revenue->id }})"
                                type="button"
                                class="btn btn-sm btn-icon btn-label-info"
                                data-bs-toggle="modal"
                                data-bs-target="#revenueEditModal"
                                title="{{ __('accounts.edit') }}"
                              >
                                <i class="ti ti-pencil"></i>
                              </button>
                            @endif
                            @if($this->canDeleteRevenueRecord($revenue))
                              <button wire:click="confirmDeleteRevenue({{ $revenue->id }})" type="button" class="btn btn-sm btn-icon btn-label-danger" title="{{ __('accounts.delete') }}">
                                <i class="ti ti-trash"></i>
                              </button>
                            @endif
                            @if($confirmedRevenueId === $revenue->id)
                              <button wire:click="deleteRevenue" type="button" class="btn btn-xs btn-danger">
                                {{ __('accounts.sure') }}
                              </button>
                            @endif
                          </span>
                        </strong>
                      </div>
                    @endif
                  </div>
                </div>
              @empty
                <div class="text-center text-muted py-5">
                  لا توجد إدخالات في هذا اليوم.
                </div>
              @endforelse
            </div>

            <div class="table-responsive day-modal-table">
              <table class="table sheet-table align-middle mb-0">
                <thead>
                  <tr>
                    <th class="text-center">{{ __('accounts.time') }}</th>
                    <th class="text-center">النوع</th>
                    @unless($this->isPerfumesAccount())
                      <th class="text-center">{{ __('accounts.employee_name') }}</th>
                    @endunless
                    <th class="text-center">{{ $this->isPerfumesAccount() ? __('accounts.perfume_name') : __('accounts.service') }}</th>
                    <th class="text-center">{{ __('accounts.cash_visa') }}</th>
                    <th class="text-center">{{ __('accounts.total') }}</th>
                    <th class="text-center">{{ __('accounts.note') }}</th>
                    <th class="text-center">{{ __('accounts.customer_name') }}</th>
                    @if($this->canShowSelectedDayRevenueActions())
                      <th class="text-center no-print">{{ __('accounts.actions') }}</th>
                    @endif
                  </tr>
                </thead>
                <tbody>
                  @forelse($selectedDayRevenues as $revenue)
                    <tr>
                      <td class="text-center">{{ $revenue->created_at?->timezone('Asia/Dubai')->format('h:i A') }}</td>
                      <td class="text-center">
                        <span class="badge bg-label-{{ ($revenue->revenue_kind ?: 'service') === 'product' ? 'success' : 'primary' }}">
                          {{ ($revenue->revenue_kind ?: 'service') === 'product' ? 'منتج' : 'خدمة' }}
                        </span>
                      </td>
                      @unless($this->isPerfumesAccount())
                        <td class="text-center">{{ $revenue->employee_name ?: '---' }}</td>
                      @endunless
                      <td class="text-center">
                        {{ (($revenue->revenue_kind ?: 'service') === 'product' && $revenue->inventoryProduct) ? $this->productEnglishNameLabel($revenue->inventoryProduct) : ($revenue->service ?: '---') }}
                      </td>
                      <td class="text-center">{{ $revenue->payment_method === 'cash' ? __('accounts.cash') : __('accounts.visa') }}</td>
                      <td class="amount-cell">AED {{ number_format((float) $revenue->amount, 2) }}</td>
                      <td>{{ $revenue->note ?: '---' }}</td>
                      <td class="text-center">{{ $revenue->customer_name ?: '---' }}</td>
                      @if($this->canShowSelectedDayRevenueActions())
                        <td class="text-center actions-cell no-print">
                          @if($this->canModifyRevenue($revenue) || $this->canDeleteRevenueRecord($revenue))
                            <div class="actions-wrap">
                              @if($this->canModifyRevenue($revenue))
                                <button
                                  wire:click="showEditRevenueModal({{ $revenue->id }})"
                                  type="button"
                                  class="btn btn-sm btn-icon btn-label-info"
                                  data-bs-toggle="modal"
                                  data-bs-target="#revenueEditModal"
                                  title="{{ __('accounts.edit') }}"
                                >
                                  <i class="ti ti-pencil"></i>
                                </button>
                              @endif
                              @if($this->canDeleteRevenueRecord($revenue))
                                <button wire:click="confirmDeleteRevenue({{ $revenue->id }})" type="button" class="btn btn-sm btn-icon btn-label-danger" title="{{ __('accounts.delete') }}">
                                  <i class="ti ti-trash"></i>
                                </button>
                              @endif
                              @if($confirmedRevenueId === $revenue->id)
                                <button wire:click="deleteRevenue" type="button" class="btn btn-xs btn-danger">
                                  {{ __('accounts.sure') }}
                                </button>
                              @endif
                            </div>
                          @else
                            <span class="badge bg-label-secondary">{{ __('accounts.record_locked') }}</span>
                          @endif
                        </td>
                      @endif
                    </tr>
                  @empty
                    <tr>
                      <td colspan="{{ ($this->isPerfumesAccount() ? 7 : 8) + ($this->canShowSelectedDayRevenueActions() ? 1 : 0) }}" class="text-center text-muted py-5">
                        لا توجد إدخالات في هذا اليوم.
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('accounts.cancel') }}</button>
          </div>
        </div>
      </div>
    </div>

    <div wire:ignore.self class="modal fade" id="monthlyTreasuryDaysModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h5 class="modal-title">رصيد الخزنة اليومي</h5>
              <small class="text-muted">شهر {{ \Carbon\Carbon::parse($selectedMonth.'-01')->translatedFormat('F Y') }}</small>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-info mb-3">
              الرصيد يبدأ من صفر مع بداية الشهر، ثم يحسب كاش كل يوم ناقص مصاريفه داخل نفس الشهر فقط.
            </div>

            <div class="table-responsive treasury-days-table">
              <table class="table sheet-table align-middle mb-0">
                <thead>
                  <tr>
                    <th class="text-center">{{ __('accounts.date') }}</th>
                    <th class="text-center">اليوم</th>
                    <th class="text-center">{{ __('accounts.cash') }}</th>
                    <th class="text-center">{{ __('accounts.total_expenses') }}</th>
                    <th class="text-center">صافي الكاش</th>
                    <th class="text-center">{{ __('accounts.cash_treasury_balance') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($monthlyTreasuryDays as $day)
                    <tr>
                      <td class="date-cell">{{ \Carbon\Carbon::parse($day['date'])->format('d-m-Y') }}</td>
                      <td class="text-center">{{ $day['label'] }}</td>
                      <td class="amount-cell text-success">AED {{ number_format($day['cash'], 2) }}</td>
                      <td class="amount-cell text-danger">AED {{ number_format($day['expenses'], 2) }}</td>
                      <td class="amount-cell {{ $day['net_cash'] >= 0 ? 'text-success' : 'text-danger' }}">
                        AED {{ number_format($day['net_cash'], 2) }}
                      </td>
                      <td class="amount-cell {{ $day['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                        AED {{ number_format($day['balance'], 2) }}
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="6" class="text-center text-muted py-5">لا توجد أيام لعرضها.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <div class="treasury-days-cards">
              @forelse($monthlyTreasuryDays as $day)
                <div class="treasury-day-card">
                  <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                    <div>
                      <div class="fw-bold">{{ $day['label'] }}</div>
                      <div class="text-muted small date-text">{{ \Carbon\Carbon::parse($day['date'])->format('d-m-Y') }}</div>
                    </div>
                    <div class="amount-cell fw-bold {{ $day['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                      AED {{ number_format($day['balance'], 2) }}
                    </div>
                  </div>
                  <div class="compact-lines">
                    <div class="compact-line">
                      <span>{{ __('accounts.cash') }}</span>
                      <strong>AED {{ number_format($day['cash'], 2) }}</strong>
                    </div>
                    <div class="compact-line">
                      <span>{{ __('accounts.total_expenses') }}</span>
                      <strong>AED {{ number_format($day['expenses'], 2) }}</strong>
                    </div>
                    <div class="compact-line">
                      <span>صافي الكاش</span>
                      <strong>AED {{ number_format($day['net_cash'], 2) }}</strong>
                    </div>
                  </div>
                </div>
              @empty
                <div class="text-center text-muted py-5">لا توجد أيام لعرضها.</div>
              @endforelse
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('accounts.cancel') }}</button>
          </div>
        </div>
      </div>
    </div>

    <div wire:ignore.self class="modal fade" id="revenueEditModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ __('accounts.edit_revenue') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">نوع الإيراد</label>
                <select wire:model.live="revenueForm.revenue_kind" class="form-select @error('revenueForm.revenue_kind') is-invalid @enderror">
                  @foreach($this->revenueKindOptions() as $kindValue => $kindLabel)
                    <option value="{{ $kindValue }}">{{ $kindLabel }}</option>
                  @endforeach
                </select>
                @error('revenueForm.revenue_kind')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              @if($this->productRevenue())
                <div class="col-md-6">
                  <label class="form-label">{{ __('accounts.product') }}</label>
                  <div wire:ignore>
                    <select id="revenueEditProductSelect" data-placeholder="{{ __('accounts.search_product') }}" class="form-select inventory-product-select @error('revenueForm.inventory_product_id') is-invalid @enderror">
                      <option value="">{{ __('accounts.search_product') }}</option>
                    @foreach($inventoryProducts as $product)
                      <option
                        value="{{ $product->id }}"
                        data-name="{{ $this->productEnglishNameLabel($product) }}"
                        data-price="{{ $this->productPriceLabel($product) }}"
                      >
                        {{ $this->productOptionLabel($product) }}
                      </option>
                    @endforeach
                    </select>
                  </div>
                  @error('revenueForm.inventory_product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              @endif

              @unless($this->isPerfumesAccount() || $this->productRevenue())
                <div class="col-md-6">
                  <label class="form-label">{{ __('accounts.employee_name') }}</label>
                  <select wire:model.defer="revenueForm.employee_name" class="form-select @error('revenueForm.employee_name') is-invalid @enderror">
                    <option value="">{{ __('accounts.employee_name') }}</option>
                    @foreach($employees as $employee)
                      @php($employeeName = $employee->full_name ?: $employee->first_name)
                      <option value="{{ $employeeName }}">{{ $employeeName }}</option>
                    @endforeach
                  </select>
                  @error('revenueForm.employee_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              @endunless
              <div class="col-md-6">
                <label class="form-label">{{ $this->productRevenue() ? 'اسم المنتج' : ($this->isPerfumesAccount() ? 'تركيبة عطر' : __('accounts.service')) }}</label>
                <input wire:model.defer="revenueForm.service" type="text" class="form-control @error('revenueForm.service') is-invalid @enderror">
                @error('revenueForm.service')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              @if($this->isPerfumesAccount() || $this->productRevenue())
                <div class="col-md-6">
                  <label class="form-label">{{ $this->productRevenue() ? 'الكمية' : __('accounts.pieces_count') }}</label>
                  @php($selectedProductAvailableStock = $this->selectedProductAvailableStock())
                  <input
                    wire:model.defer="revenueForm.quantity"
                    type="number"
                    min="{{ $this->selectedProductUnit() === 'piece' || $this->isPerfumesAccount() ? '1' : '0.001' }}"
                    @if($this->productRevenue() && $selectedProductAvailableStock !== null)
                      max="{{ $selectedProductAvailableStock }}"
                    @endif
                    step="{{ $this->selectedProductUnit() === 'piece' || $this->isPerfumesAccount() ? '1' : '0.001' }}"
                    inputmode="{{ $this->selectedProductUnit() === 'piece' || $this->isPerfumesAccount() ? 'numeric' : 'decimal' }}"
                    class="form-control amount-cell @error('revenueForm.quantity') is-invalid @enderror"
                  >
                  @if($this->productRevenue() && $selectedProductAvailableStock !== null)
                    <div class="form-text text-muted">المتاح: {{ $this->productQuantityLabel($selectedProductAvailableStock, $inventoryProducts->firstWhere('id', (int) $revenueForm['inventory_product_id'])) }}</div>
                  @endif
                  @error('revenueForm.quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ __('accounts.unit_price') }}</label>
                  <input wire:model.defer="revenueForm.unit_price" type="number" min="0.01" step="0.01" class="form-control amount-cell @error('revenueForm.unit_price') is-invalid @enderror">
                  @error('revenueForm.unit_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              @else
                <div class="col-md-6">
                  <label class="form-label">{{ __('accounts.service_price') }}</label>
                  <input wire:model.defer="revenueForm.amount" type="number" min="0.01" step="0.01" class="form-control amount-cell @error('revenueForm.amount') is-invalid @enderror">
                  @error('revenueForm.amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              @endif
              <div class="col-md-6">
                <label class="form-label">{{ __('accounts.cash_visa') }}</label>
                <select wire:model.defer="revenueForm.payment_method" class="form-select @error('revenueForm.payment_method') is-invalid @enderror">
                  <option value="cash">{{ __('accounts.cash') }}</option>
                  <option value="visa">{{ __('accounts.visa') }}</option>
                </select>
                @error('revenueForm.payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              @if($this->canEditRevenueDateTime())
                <div class="col-md-6">
                  <label class="form-label">{{ __('accounts.date') }}</label>
                  <input wire:model.defer="revenueForm.date" type="date" dir="ltr" lang="en" class="form-control @error('revenueForm.date') is-invalid @enderror">
                  @error('revenueForm.date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label">{{ __('accounts.time') }}</label>
                  <input wire:model.defer="revenueForm.time" type="time" class="form-control @error('revenueForm.time') is-invalid @enderror">
                  @error('revenueForm.time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              @endif
              <div class="col-md-6">
                <label class="form-label">{{ __('accounts.customer_name') }}</label>
                <input wire:model.defer="revenueForm.customer_name" type="text" class="form-control @error('revenueForm.customer_name') is-invalid @enderror">
                @error('revenueForm.customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-12">
                <label class="form-label">{{ __('accounts.note') }}</label>
                <textarea wire:model.defer="revenueForm.note" class="form-control @error('revenueForm.note') is-invalid @enderror"></textarea>
                @error('revenueForm.note')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('accounts.cancel') }}</button>
            <button wire:click="updateRevenue" type="button" class="btn btn-primary">{{ __('accounts.save_changes') }}</button>
          </div>
        </div>
      </div>
    </div>

    <div wire:ignore.self class="modal fade" id="revenueNoteModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ __('accounts.note') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="text-wrap lh-lg">{{ $viewingNote }}</div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('accounts.cancel') }}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@push('custom-scripts')
  <script>
    window.addEventListener('openModal', event => {
      $(event.detail.elementId).modal('show');
    });

    (() => {
      const formatProductOption = (state) => {
        if (!state.id) {
          return state.text;
        }

        const option = state.element;
        const name = option?.dataset?.name || state.text;
        const price = option?.dataset?.price || '';

        return $(`
          <span class="revenue-product-option">
            <span class="product-name"></span>
            <span class="product-price"></span>
          </span>
        `)
          .find('.product-name').text(name).end()
          .find('.product-price').text(price).end();
      };

      const syncProductSelectValue = ($select) => {
        const currentValue = @this.get('revenueForm.inventory_product_id') || '';
        $select.val(String(currentValue)).trigger('change.select2');
      };

      const initProductSelects = () => {
        $('.inventory-product-select').each(function () {
          const $select = $(this);

          if (!$select.hasClass('select2-hidden-accessible')) {
            const $modal = $select.closest('.modal');

            $select.select2({
              allowClear: true,
              dir: '{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}',
              width: '100%',
              minimumInputLength: 1,
              placeholder: $select.data('placeholder') || @js(__('accounts.search_product')),
              dropdownParent: $modal.length ? $modal : $(document.body),
              templateResult: formatProductOption,
              templateSelection: formatProductOption,
              language: {
                inputTooShort: () => 'اكتب أول حرف للبحث',
                noResults: () => 'لا توجد منتجات',
                searching: () => 'جاري البحث...'
              }
            });

            $select.on('change.revenueProduct', function () {
              @this.set('revenueForm.inventory_product_id', $(this).val() || null);
            });
          }

          syncProductSelectValue($select);
        });
      };

      const debouncedInitProductSelects = (() => {
        let timer;

        return () => {
          clearTimeout(timer);
          timer = setTimeout(initProductSelects, 30);
        };
      })();

      const persistedCollapses = [
        {
          id: 'desktopReportsCollapse',
          key: 'accounts.revenues.reports.open',
          button: '[data-bs-target="#desktopReportsCollapse"]'
        },
        {
          id: 'revenueFormCollapse',
          key: 'accounts.revenues.form.open',
          button: '[data-bs-target="#revenueFormCollapse"]'
        }
      ];

      const setCollapseButtonState = (buttonSelector, isOpen) => {
        document.querySelectorAll(buttonSelector).forEach((button) => {
          button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
          button.classList.toggle('collapsed', !isOpen);
        });
      };

      const restoreCollapseState = () => {
        persistedCollapses.forEach(({ id, key, button }) => {
          const element = document.getElementById(id);

          if (!element || element.dataset.persistBound === '1') {
            if (element && localStorage.getItem(key) === '1') {
              element.classList.add('show');
              setCollapseButtonState(button, true);
            }

            return;
          }

          element.dataset.persistBound = '1';

          element.addEventListener('shown.bs.collapse', () => {
            localStorage.setItem(key, '1');
            setCollapseButtonState(button, true);
          });

          element.addEventListener('hidden.bs.collapse', () => {
            localStorage.setItem(key, '0');
            setCollapseButtonState(button, false);
          });

          if (localStorage.getItem(key) === '1') {
            element.classList.add('show');
            setCollapseButtonState(button, true);
          }
        });
      };

      const debouncedRestoreCollapseState = (() => {
        let timer;

        return () => {
          clearTimeout(timer);
          timer = setTimeout(restoreCollapseState, 0);
        };
      })();

      const shouldReinitProductSelects = (mutations) => mutations.some((mutation) => {
        const changedNodes = [...mutation.addedNodes, ...mutation.removedNodes];

        return changedNodes.some((node) => {
          if (!(node instanceof Element)) {
            return false;
          }

          return node.matches('.inventory-product-select')
            || node.querySelector('.inventory-product-select');
        });
      });

      document.addEventListener('livewire:init', () => {
        initProductSelects();
        restoreCollapseState();

        try {
          Livewire.hook('morph.updated', () => {
            debouncedRestoreCollapseState();
          });
        } catch (error) {
          // Older Livewire builds do not expose this hook.
        }
      });

      const observer = new MutationObserver((mutations) => {
        if (shouldReinitProductSelects(mutations)) {
          debouncedInitProductSelects();
        }

        debouncedRestoreCollapseState();
      });
      observer.observe(document.querySelector('.revenues-page') || document.body, { childList: true, subtree: true });

      initProductSelects();
      restoreCollapseState();
    })();
  </script>
@endpush
