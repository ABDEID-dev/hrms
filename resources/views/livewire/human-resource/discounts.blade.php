<div dir="rtl">
  @section('title', 'الخصومات')

  @section('page-style')
    <style>
      .simple-discounts .amount-input {
        direction: ltr;
        text-align: left;
      }

      .simple-discounts .discount-reason {
        white-space: normal;
        min-width: 220px;
      }

      .simple-discounts .quick-date-picker {
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

      .simple-discounts .quick-date-input {
        width: 168px;
        direction: ltr;
        text-align: center;
        font-weight: 700;
      }

      .simple-discounts .quick-date-meta {
        min-width: 115px;
        text-align: center;
        color: var(--bs-secondary-color);
        font-size: .82rem;
      }

      .simple-discounts .print-only {
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

        .simple-discounts,
        .simple-discounts * {
          visibility: visible;
        }

        .simple-discounts {
          position: absolute;
          inset: 0;
          width: 100%;
          margin: 0 !important;
          background: #fff !important;
          color: #111 !important;
        }

        .simple-discounts .no-print,
        .simple-discounts button {
          display: none !important;
        }

        .simple-discounts .print-only {
          display: block !important;
        }

        .simple-discounts .print-title {
          margin-bottom: 4mm;
          padding-bottom: 3mm;
          border-bottom: 1px solid #222;
          text-align: center;
        }

        .simple-discounts .print-title h4 {
          margin: 0 0 1mm !important;
          font-size: 11pt;
          font-weight: 800;
        }

        .simple-discounts .print-summary {
          border: 1px solid #c8ced6 !important;
          border-radius: 2mm;
          padding: 2mm 2.5mm !important;
          margin-bottom: 3mm;
          background: #f7f9fb !important;
        }

        .simple-discounts .card {
          border: 0 !important;
          box-shadow: none !important;
          margin-bottom: 4mm !important;
          background: transparent !important;
        }

        .simple-discounts .card-header {
          display: block !important;
          padding: 0 0 2mm !important;
          margin-bottom: 2mm;
          border-bottom: 1px solid #d8dde3 !important;
        }

        .simple-discounts .table-responsive {
          overflow: visible !important;
        }

        .simple-discounts .table {
          width: 100% !important;
          min-width: 0 !important;
          border-collapse: collapse !important;
          table-layout: fixed;
          font-size: 5.9pt;
        }

        .simple-discounts .table th {
          background: #edf1f5 !important;
          color: #000 !important;
          border: 1px solid #9fa8b3 !important;
          padding: .9mm .55mm !important;
          white-space: normal;
        }

        .simple-discounts .table td {
          border: 1px solid #c8ced6 !important;
          padding: .8mm .5mm !important;
          background: #fff !important;
          color: #111 !important;
        }

        .simple-discounts .badge,
        .simple-discounts .text-muted {
          color: #111 !important;
          background: transparent !important;
        }
      }

      @media (max-width: 767.98px) {
        .simple-discounts {
          margin-inline: -.75rem;
        }

        .simple-discounts .card {
          border-radius: 0;
        }

        .simple-discounts .quick-date-picker {
          display: grid;
          grid-template-columns: 44px minmax(0, 1fr) 44px;
          gap: .5rem;
          width: 100%;
          padding: .65rem;
          border-radius: .5rem;
        }

        .simple-discounts .quick-date-picker .btn-icon {
          width: 44px;
          height: 44px;
        }

        .simple-discounts .quick-date-input {
          width: 100%;
          min-width: 0;
          height: 44px;
          font-size: 1rem;
        }

        .simple-discounts .quick-date-picker .btn-label-secondary,
        .simple-discounts .quick-date-meta {
          grid-column: 1 / -1;
          width: 100%;
        }

        .simple-discounts .quick-date-meta {
          padding-top: .15rem;
          font-size: .78rem;
        }
      }
    </style>
  @endsection

  <div class="no-print">
    @include('_partials/_alerts/alert-general')
  </div>

  <div class="simple-discounts">
    <div class="print-only print-title">
      <h4>تقرير الخصومات</h4>
      <div>{{ \Carbon\Carbon::parse($selectedDate)->format('d-m-Y') }}</div>
    </div>

    <div class="print-only print-summary">
      <strong>إجمالي الخصومات:</strong>
      AED {{ number_format((float) $discounts->sum('rate'), 2) }}
      <span class="mx-2">|</span>
      <strong>عدد السجلات:</strong>
      {{ $discounts->count() }}
    </div>

    <div class="card mb-4 no-print">
      <div class="card-body text-center">
        <div class="quick-date-picker">
          <button wire:click="showPreviousDay" type="button" class="btn btn-label-danger btn-icon" title="اليوم السابق">
            <i class="ti ti-chevron-right"></i>
          </button>
          <input
            wire:model.live="selectedDate"
            type="date"
            max="{{ \Carbon\Carbon::today('Asia/Dubai')->toDateString() }}"
            class="form-control quick-date-input"
          >
          <button wire:click="showNextDay" type="button" class="btn btn-label-danger btn-icon" title="اليوم التالي" @disabled(! $this->canShowNextDay())>
            <i class="ti ti-chevron-left"></i>
          </button>
          <button wire:click="showToday" type="button" class="btn btn-label-secondary">
            اليوم الحالي
          </button>
          <div class="quick-date-meta">
            {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l') }}
            @if($selectedDate === \Carbon\Carbon::today('Asia/Dubai')->toDateString())
              <div class="text-success small">اليوم الحالي</div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4 no-print">
      <div class="card-header border-bottom">
        <h5 class="mb-0">إضافة خصم جديد</h5>
      </div>
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-lg-4 col-md-6">
            <label class="form-label">الموظف</label>
            <select wire:model="selectedEmployeeId" class="form-select @error('selectedEmployeeId') is-invalid @enderror">
              @forelse($employees as $employee)
                <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
              @empty
                <option value="">لا يوجد موظفون</option>
              @endforelse
            </select>
            @error('selectedEmployeeId')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-lg-2 col-md-6">
            <label class="form-label">مبلغ الخصم</label>
            <input
              wire:model.defer="discountForm.amount"
              type="number"
              min="1"
              class="form-control amount-input @error('discountForm.amount') is-invalid @enderror"
              placeholder="0"
            >
            @error('discountForm.amount')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-lg-4 col-md-8">
            <label class="form-label">سبب الخصم</label>
            <input
              wire:model.defer="discountForm.reason"
              type="text"
              class="form-control @error('discountForm.reason') is-invalid @enderror"
              placeholder="مثال: تأخير، غياب، مخالفة..."
            >
            @error('discountForm.reason')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <div class="col-lg-2 col-md-4">
            <button wire:click="createDiscount" type="button" class="btn btn-primary w-100">
              <i class="ti ti-plus me-1"></i>
              إضافة الخصم
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0">سجل الخصومات</h5>
        <div class="d-flex align-items-center gap-2 no-print">
          <button onclick="window.print()" type="button" class="btn btn-sm btn-label-secondary">
            <i class="ti ti-printer me-1"></i>
            طباعة
          </button>
          <span class="badge bg-label-danger">{{ $discounts->count() }} خصم</span>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr>
              <th>الموظف</th>
              <th class="text-center">مبلغ الخصم</th>
              <th>السبب</th>
              <th class="text-center">التاريخ</th>
              <th class="text-center no-print">الإجراءات</th>
            </tr>
          </thead>
          <tbody>
            @forelse($discounts as $discount)
              <tr>
                <td>
                  <div class="fw-semibold">{{ $discount->employee?->full_name ?? '---' }}</div>
                  <small class="text-muted">#{{ $discount->employee_id }}</small>
                </td>
                <td class="text-center no-print">
                  <span class="badge bg-label-danger">{{ $discount->rate }}</span>
                </td>
                <td class="discount-reason">{{ $discount->reason }}</td>
                <td class="text-center">{{ $discount->date }}</td>
                <td class="text-center">
                  <button
                    wire:click="confirmDeleteDiscount({{ $discount->id }})"
                    type="button"
                    class="btn btn-sm btn-icon btn-label-danger"
                    title="حذف الخصم"
                  >
                    <i class="ti ti-trash"></i>
                  </button>

                  @if($confirmedDiscountId === $discount->id)
                    <button wire:click="deleteDiscount" type="button" class="btn btn-xs btn-danger">
                      {{ __('Sure?') }}
                    </button>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-5">
                  لا توجد خصومات حتى الآن.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
