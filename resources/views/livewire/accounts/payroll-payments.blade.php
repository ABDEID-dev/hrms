<div dir="rtl">
  @section('title', 'تسديد المرتبات')

  @section('page-style')
    <style>
      .payroll-page .summary-card {
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .5rem;
        background: rgba(var(--bs-body-bg-rgb), .45);
        padding: 1rem;
        height: 100%;
      }

      .payroll-page .summary-label {
        color: var(--bs-secondary-color);
        font-size: .82rem;
      }

      .payroll-page .money,
      .payroll-page .summary-value,
      .payroll-page input[type="month"] {
        direction: ltr;
        unicode-bidi: isolate;
      }

      .payroll-page .summary-value {
        margin-top: .35rem;
        font-size: 1.14rem;
        font-weight: 800;
        white-space: nowrap;
      }

      .payroll-page .sheet-table th {
        background: #24455a;
        color: #fff;
        white-space: nowrap;
        vertical-align: middle;
      }

      .payroll-page .sheet-table td {
        vertical-align: middle;
      }

      .payroll-page .amount-input {
        min-width: 125px;
        direction: ltr;
        text-align: center;
      }

      .payroll-page .employee-card {
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .55rem;
        background: rgba(var(--bs-body-bg-rgb), .38);
        padding: .9rem;
      }

      .payroll-page .mini-line {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        padding: .42rem 0;
        border-bottom: 1px solid rgba(var(--bs-border-color-rgb), .25);
      }

      .payroll-page .mini-line:last-child {
        border-bottom: 0;
      }

      @media (max-width: 767.98px) {
        .payroll-page {
          margin-inline: -.75rem;
        }

        .payroll-page .card {
          border-radius: 0;
        }

        .payroll-page .summary-card {
          text-align: center;
          padding: .85rem;
        }
      }
    </style>
  @endsection

  <div class="payroll-page">
    @include('_partials/_alerts/alert-general')

    <div class="card mb-4">
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
          <h5 class="mb-1">تسديد المرتبات</h5>
          <small class="text-muted">اختر الشهر، أضف المبلغ المحول، ثم سدد رواتب الموظفين المتبقية من إجمالي التحويل وخزنة مكتوم.</small>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <input wire:model.defer="selectedMonth" type="month" class="form-control" style="min-width: 170px;">
          <button wire:click="applyMonth" type="button" class="btn btn-primary">
            <i class="ti ti-calendar-stats me-1"></i>
            عرض الشهر
          </button>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-6 col-xl-3">
        <div class="summary-card">
          <div class="summary-label">المحول لهذا الشهر</div>
          <div class="summary-value text-info">AED {{ number_format($summary['month_transfers'], 2) }}</div>
        </div>
      </div>
      <div class="col-6 col-xl-3">
        <div class="summary-card">
          <div class="summary-label">رصيد خزنة مكتوم في نهاية شهر الراتب</div>
          <div class="summary-value text-success">AED {{ number_format($summary['maktoom_treasury'], 2) }}</div>
        </div>
      </div>
      <div class="col-6 col-xl-3">
        <div class="summary-card">
          <div class="summary-label">المتاح للسداد لهذا الشهر</div>
          <div class="summary-value {{ $summary['available'] >= 0 ? 'text-success' : 'text-danger' }}">AED {{ number_format($summary['available'], 2) }}</div>
        </div>
      </div>
      <div class="col-6 col-xl-3">
        <div class="summary-card">
          <div class="summary-label">المتبقي للموظفين</div>
          <div class="summary-value text-warning">AED {{ number_format($summary['remaining'], 2) }}</div>
        </div>
      </div>
      <div class="col-6 col-xl-3">
        <div class="summary-card">
          <div class="summary-label">المطلوب بعد السحوبات</div>
          <div class="summary-value">AED {{ number_format($summary['required'], 2) }}</div>
        </div>
      </div>
      <div class="col-6 col-xl-3">
        <div class="summary-card">
          <div class="summary-label">تم تسديده</div>
          <div class="summary-value text-success">AED {{ number_format($summary['paid'], 2) }}</div>
        </div>
      </div>
      <div class="col-6 col-xl-3">
        <div class="summary-card">
          <div class="summary-label">السحوبات / الخصومات</div>
          <div class="summary-value text-danger">AED {{ number_format($summary['discounts'], 2) }}</div>
        </div>
      </div>
      <div class="col-6 col-xl-3">
        <div class="summary-card">
          <div class="summary-label">عدد الموظفين في القائمة</div>
          <div class="summary-value">{{ $summary['employees_count'] }}</div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h5 class="mb-0">إضافة تحويل مرتبات لشهر <span class="money">{{ $selectedMonth }}</span></h5>
      </div>
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-md-3">
            <label class="form-label">المبلغ المحول</label>
            <input wire:model.defer="transferAmount" type="number" min="0.01" step="0.01" class="form-control amount-input @error('transferAmount') is-invalid @enderror">
            @error('transferAmount')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-7">
            <label class="form-label">ملاحظة</label>
            <input wire:model.defer="transferNote" type="text" class="form-control @error('transferNote') is-invalid @enderror" placeholder="مثال: تحويل مرتبات شهر 5">
            @error('transferNote')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-md-2">
            <button wire:click="addTransfer" type="button" class="btn btn-info w-100">
              <i class="ti ti-plus me-1"></i>
              إضافة
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
          <h5 class="mb-1">الموظفون ورواتبهم المتبقية</h5>
          <small class="text-muted">تظهر الرواتب المتبقية للشهر المحدد، وكل تسديد ينقص من خزنة مكتوم والإجمالي المتاح.</small>
        </div>
        <input wire:model.live.debounce.300ms="search" type="text" class="form-control" style="max-width: 280px;" placeholder="بحث عن موظف">
      </div>

      <div class="table-responsive d-none d-md-block">
        <table class="table sheet-table table-hover mb-0">
          <thead>
            <tr>
              <th>الموظف</th>
              <th>الوظيفة</th>
              <th class="text-center">الراتب</th>
              <th class="text-center">السحوبات</th>
              <th class="text-center">المطلوب</th>
              <th class="text-center">المسدد</th>
              <th class="text-center">المتبقي</th>
              <th class="text-center">تسديد</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rows as $row)
              <tr>
                <td class="fw-semibold">{{ $row['name'] }}</td>
                <td>{{ $row['position'] ?: '---' }}</td>
                <td class="text-center money">AED {{ number_format($row['gross'], 2) }}</td>
                <td class="text-center money text-danger">AED {{ number_format($row['discounts'], 2) }}</td>
                <td class="text-center money">AED {{ number_format($row['required'], 2) }}</td>
                <td class="text-center money text-success">AED {{ number_format($row['paid'], 2) }}</td>
                <td class="text-center money text-warning">AED {{ number_format($row['remaining'], 2) }}</td>
                <td>
                  @if($row['remaining'] <= 0)
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                      <span class="badge bg-label-success">تم السداد</span>
                      @if($this->canCancelSalaryPayments())
                        @if($confirmedCancelEmployeeId === $row['id'])
                          <button wire:click="cancelSalaryPayment({{ $row['id'] }})" type="button" class="btn btn-danger btn-sm">تأكيد الإلغاء</button>
                        @else
                          <button wire:click="confirmCancelSalaryPayment({{ $row['id'] }})" type="button" class="btn btn-label-danger btn-sm">
                            <i class="ti ti-arrow-back-up me-1"></i>
                            إلغاء السداد
                          </button>
                        @endif
                      @endif
                    </div>
                  @elseif($confirmedEmployeeId === $row['id'])
                    <div class="d-flex justify-content-center gap-2">
                      <input wire:model.defer="payAmounts.{{ $row['id'] }}" type="number" min="0.01" step="0.01" class="form-control amount-input @error('payAmounts.'.$row['id']) is-invalid @enderror">
                      <button wire:click="payEmployee({{ $row['id'] }})" type="button" class="btn btn-success btn-sm">تأكيد</button>
                      <button wire:click="cancelPay" type="button" class="btn btn-label-secondary btn-sm">إلغاء</button>
                    </div>
                    @error('payAmounts.'.$row['id'])<div class="text-danger small mt-1 text-center">{{ $message }}</div>@enderror
                  @else
                    <button wire:click="preparePay({{ $row['id'] }})" type="button" class="btn btn-sm btn-primary" @disabled($summary['available'] <= 0)>
                      <i class="ti ti-cash me-1"></i>
                      سداد
                    </button>
                    @if($this->canCancelSalaryPayments() && $row['paid'] > 0)
                      @if($confirmedCancelEmployeeId === $row['id'])
                        <button wire:click="cancelSalaryPayment({{ $row['id'] }})" type="button" class="btn btn-danger btn-sm ms-1">تأكيد الإلغاء</button>
                      @else
                        <button wire:click="confirmCancelSalaryPayment({{ $row['id'] }})" type="button" class="btn btn-label-danger btn-sm ms-1">
                          <i class="ti ti-arrow-back-up me-1"></i>
                          إلغاء السداد
                        </button>
                      @endif
                    @endif
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="text-center text-muted py-5">لا توجد رواتب متبقية لهذا الشهر.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="d-md-none p-3">
        @forelse($rows as $row)
          <div class="employee-card mb-3">
            <div class="d-flex justify-content-between gap-3 mb-2">
              <div>
                <div class="fw-bold">{{ $row['name'] }}</div>
                <small class="text-muted">{{ $row['position'] ?: '---' }}</small>
              </div>
              <div class="money text-warning">AED {{ number_format($row['remaining'], 2) }}</div>
            </div>
            <div class="mini-line"><span>الراتب</span><span class="money">AED {{ number_format($row['gross'], 2) }}</span></div>
            <div class="mini-line"><span>السحوبات</span><span class="money text-danger">AED {{ number_format($row['discounts'], 2) }}</span></div>
            <div class="mini-line"><span>المطلوب</span><span class="money">AED {{ number_format($row['required'], 2) }}</span></div>
            <div class="mini-line"><span>المسدد</span><span class="money text-success">AED {{ number_format($row['paid'], 2) }}</span></div>

            @if($row['remaining'] <= 0)
              <div class="d-grid gap-2 mt-3">
                <span class="badge bg-label-success">تم السداد</span>
                @if($this->canCancelSalaryPayments())
                  @if($confirmedCancelEmployeeId === $row['id'])
                    <button wire:click="cancelSalaryPayment({{ $row['id'] }})" type="button" class="btn btn-danger">تأكيد إلغاء السداد</button>
                  @else
                    <button wire:click="confirmCancelSalaryPayment({{ $row['id'] }})" type="button" class="btn btn-label-danger">
                      إلغاء السداد
                    </button>
                  @endif
                @endif
              </div>
            @elseif($confirmedEmployeeId === $row['id'])
              <div class="d-grid gap-2 mt-3">
                <input wire:model.defer="payAmounts.{{ $row['id'] }}" type="number" min="0.01" step="0.01" class="form-control amount-input @error('payAmounts.'.$row['id']) is-invalid @enderror">
                @error('payAmounts.'.$row['id'])<div class="text-danger small">{{ $message }}</div>@enderror
                <button wire:click="payEmployee({{ $row['id'] }})" type="button" class="btn btn-success">تأكيد السداد</button>
                <button wire:click="cancelPay" type="button" class="btn btn-label-secondary">إلغاء</button>
              </div>
            @else
              <button wire:click="preparePay({{ $row['id'] }})" type="button" class="btn btn-primary w-100 mt-3" @disabled($summary['available'] <= 0)>
                سداد
              </button>
              @if($this->canCancelSalaryPayments() && $row['paid'] > 0)
                @if($confirmedCancelEmployeeId === $row['id'])
                  <button wire:click="cancelSalaryPayment({{ $row['id'] }})" type="button" class="btn btn-danger w-100 mt-2">تأكيد إلغاء السداد</button>
                @else
                  <button wire:click="confirmCancelSalaryPayment({{ $row['id'] }})" type="button" class="btn btn-label-danger w-100 mt-2">
                    إلغاء السداد
                  </button>
                @endif
              @endif
            @endif
          </div>
        @empty
          <div class="text-center text-muted py-4">لا توجد رواتب متبقية لهذا الشهر.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>
