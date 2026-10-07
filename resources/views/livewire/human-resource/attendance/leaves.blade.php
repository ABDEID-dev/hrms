<div dir="rtl">
  @section('title', 'الإجازات الأسبوعية')

  @section('page-style')
    <style>
      .weekly-holiday-page .weekday-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(70px, 1fr));
        gap: .5rem;
      }

      .weekly-holiday-page .day-option,
      .weekly-holiday-page .day-cell {
        border: 1px solid var(--bs-border-color);
        border-radius: .5rem;
        min-height: 48px;
      }

      .weekly-holiday-page .day-option {
        background: transparent;
        color: var(--bs-body-color);
      }

      .weekly-holiday-page .day-option.active,
      .weekly-holiday-page .day-cell.active {
        border-color: var(--bs-primary);
        background: rgba(var(--bs-primary-rgb), .16);
        color: var(--bs-primary);
        font-weight: 700;
      }

      .weekly-holiday-page .employee-name {
        min-width: 220px;
      }

      .weekly-holiday-page .mobile-employee-list {
        display: none;
      }

      .weekly-holiday-page .create-holiday-button {
        min-height: 48px;
        white-space: nowrap;
      }

      @media (max-width: 991.98px) {
        .weekly-holiday-page .weekday-grid {
          grid-template-columns: repeat(3, minmax(0, 1fr));
          gap: .5rem;
        }

        .weekly-holiday-page .employee-name {
          min-width: 170px;
        }

        .weekly-holiday-page .day-option {
          min-height: 44px;
          padding-inline: .5rem !important;
          font-size: .9rem;
        }
      }

      @media (max-width: 767.98px) {
        .weekly-holiday-page {
          margin-inline: -.75rem;
        }

        .weekly-holiday-page .card {
          border-radius: 0;
        }

        .weekly-holiday-page .card-header {
          align-items: flex-start !important;
          gap: .75rem;
        }

        .weekly-holiday-page .weekday-grid {
          grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .weekly-holiday-page .desktop-holiday-table {
          display: none;
        }

        .weekly-holiday-page .mobile-employee-list {
          display: grid;
          gap: .75rem;
          padding: 1rem;
        }

        .weekly-holiday-page .mobile-employee-card {
          border: 1px solid var(--bs-border-color);
          border-radius: .5rem;
          padding: .875rem;
        }

        .weekly-holiday-page .mobile-holiday-pill {
          border: 1px solid var(--bs-primary);
          border-radius: .5rem;
          background: rgba(var(--bs-primary-rgb), .16);
          color: var(--bs-primary);
          display: inline-flex;
          align-items: center;
          justify-content: center;
          min-height: 40px;
          padding: .5rem .875rem;
          font-weight: 700;
          margin-top: .75rem;
          width: 100%;
        }
      }
    </style>
  @endsection

  @include('_partials/_alerts/alert-general')

  <div class="weekly-holiday-page">
    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h5 class="mb-0">إنشاء إجازة أسبوعية</h5>
      </div>
      <div class="card-body">
        <div class="row g-4 align-items-end">
          <div class="col-xl-3 col-lg-4 col-md-6">
            <label class="form-label">الموظف</label>
            <select wire:model="selectedEmployeeId" class="form-select">
              @forelse($employees as $employee)
                <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
              @empty
                <option value="">لا يوجد موظفون</option>
              @endforelse
            </select>
          </div>

          <div class="col-xl-7 col-lg-8">
            <label class="form-label">يوم الإجازة</label>
            <div class="weekday-grid">
              @foreach($daysOfWeek as $dayValue => $dayName)
                <button
                  type="button"
                  wire:click="$set('selectedDay', {{ $dayValue }})"
                  class="day-option px-3 py-2 {{ (int) $selectedDay === (int) $dayValue ? 'active' : '' }}"
                >
                  {{ $dayName }}
                </button>
              @endforeach
            </div>
          </div>

          <div class="col-xl-2 col-lg-4 col-md-6">
            <button wire:click="createWeeklyHoliday" type="button" class="btn btn-primary w-100 create-holiday-button">
              <i class="ti ti-plus me-1"></i>
              إنشاء الإجازة
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0">لوحة إجازات الموظفين</h5>
        <span class="badge bg-label-primary">{{ $employees->count() }} موظف</span>
      </div>

      <div class="table-responsive desktop-holiday-table">
        <table class="table align-middle mb-0">
          <thead>
            <tr>
              <th class="employee-name">الموظف</th>
              @foreach($daysOfWeek as $dayName)
                <th class="text-center">{{ $dayName }}</th>
              @endforeach
              <th class="text-center">الإجراء</th>
            </tr>
          </thead>
          <tbody>
            @forelse($employees as $employee)
              <tr>
                <td class="employee-name">
                  <div class="fw-semibold">{{ $employee->full_name }}</div>
                  <small class="text-muted">{{ $this->getHolidayName($employee->weekly_holiday) }}</small>
                </td>

                @foreach($daysOfWeek as $dayValue => $dayName)
                  <td class="text-center">
                    <div class="day-cell d-flex align-items-center justify-content-center {{ $employee->weekly_holiday !== null && (int) $employee->weekly_holiday === (int) $dayValue ? 'active' : '' }}">
                      @if($employee->weekly_holiday !== null && (int) $employee->weekly_holiday === (int) $dayValue)
                        إجازة
                      @else
                        -
                      @endif
                    </div>
                  </td>
                @endforeach

                <td class="text-center">
                  @if($employee->weekly_holiday !== null)
                    <button wire:click="clearWeeklyHoliday({{ $employee->id }})" type="button" class="btn btn-sm btn-label-danger">
                      إزالة
                    </button>
                  @else
                    <span class="text-muted">-</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center py-5">
                  لا يوجد موظفون نشطون حاليا.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mobile-employee-list">
        @forelse($employees as $employee)
          <div class="mobile-employee-card">
            <div class="d-flex justify-content-between align-items-start gap-2">
              <div>
                <div class="fw-semibold">{{ $employee->full_name }}</div>
                <small class="text-muted">{{ $this->getHolidayName($employee->weekly_holiday) }}</small>
              </div>

              @if($employee->weekly_holiday !== null)
                <button wire:click="clearWeeklyHoliday({{ $employee->id }})" type="button" class="btn btn-sm btn-label-danger">
                  إزالة
                </button>
              @endif
            </div>

            <div class="mobile-holiday-pill">
              {{ $employee->weekly_holiday !== null ? $this->getHolidayName($employee->weekly_holiday) . ' - إجازة' : 'لم يتم تحديد إجازة' }}
            </div>
          </div>
        @empty
          <div class="text-center text-muted py-4">
            لا يوجد موظفون نشطون حاليا.
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>
