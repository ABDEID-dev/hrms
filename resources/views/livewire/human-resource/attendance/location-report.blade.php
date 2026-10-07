<div>
  @section('title', 'تتبع الموظفين')

  @section('page-style')
    <style>
      .attendance-location-page input[type="date"],
      .attendance-location-page .date-text {
          direction: ltr;
          unicode-bidi: plaintext;
          white-space: nowrap;
      }

      .attendance-location-page .summary-card,
      .attendance-location-page .employee-track-card,
      .attendance-location-page .track-step,
      .attendance-location-page .system-open-event {
          border: 1px solid rgba(var(--bs-border-color-rgb), 0.34);
          border-radius: 0.75rem;
          background: rgba(var(--bs-body-bg-rgb), 0.45);
      }

      .attendance-location-page .summary-card {
          padding: 1rem;
      }

      .attendance-location-page .summary-value {
          font-size: 1.35rem;
          font-weight: 800;
      }

      .attendance-location-page .employee-track-card {
          overflow: hidden;
      }

      .attendance-location-page .employee-track-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          gap: 1rem;
          padding: 1rem 1.15rem;
          border-bottom: 1px solid rgba(var(--bs-border-color-rgb), 0.28);
          background: rgba(115, 103, 240, 0.08);
      }

      .attendance-location-page .employee-avatar {
          width: 48px;
          height: 48px;
          object-fit: cover;
          border: 2px solid rgba(255, 255, 255, 0.22);
      }

      .attendance-location-page .employee-meta {
          display: grid;
          grid-template-columns: repeat(3, minmax(0, 1fr));
          gap: 0.75rem;
          padding: 0.9rem 1.15rem 0;
      }

      .attendance-location-page .employee-meta-item {
          padding: 0.7rem 0.8rem;
          border-radius: 0.65rem;
          background: rgba(255, 255, 255, 0.035);
      }

      .attendance-location-page .track-grid {
          display: grid;
          grid-template-columns: repeat(3, minmax(0, 1fr));
          gap: 0.85rem;
          padding: 1rem 1.15rem 1.15rem;
      }

      .attendance-location-page .track-step {
          padding: 1rem;
          min-height: 165px;
          display: flex;
          flex-direction: column;
          gap: 0.7rem;
      }

      .attendance-location-page .track-step.is-missing {
          opacity: 0.72;
      }

      .attendance-location-page .track-step-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          gap: 0.75rem;
      }

      .attendance-location-page .track-icon {
          width: 34px;
          height: 34px;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          border-radius: 0.65rem;
          background: rgba(115, 103, 240, 0.16);
      }

      .attendance-location-page .track-title {
          font-weight: 800;
          line-height: 1.2;
      }

      .attendance-location-page .track-time {
          font-size: 0.95rem;
          font-weight: 800;
      }

      .attendance-location-page .coord {
          direction: ltr;
          unicode-bidi: plaintext;
          font-size: 0.9rem;
          font-weight: 800;
          word-break: break-word;
      }

      .attendance-location-page .map-btn {
          margin-top: auto;
          justify-content: center;
      }

      .attendance-location-page .tracking-empty {
          border: 1px dashed rgba(var(--bs-border-color-rgb), 0.45);
          border-radius: 0.75rem;
          padding: 1.15rem;
          text-align: center;
          color: var(--bs-secondary-color);
          background: rgba(255, 255, 255, 0.025);
      }

      .attendance-location-page .event-list {
          display: grid;
          grid-template-columns: repeat(2, minmax(0, 1fr));
          gap: 0.85rem;
      }

      .attendance-location-page .system-open-event {
          padding: 1rem;
      }

      .attendance-location-page .event-user {
          display: flex;
          align-items: center;
          gap: 0.75rem;
      }

      .attendance-location-page .event-avatar {
          width: 38px;
          height: 38px;
          object-fit: cover;
      }

      @media (max-width: 767.98px) {
          .attendance-location-page {
              padding-top: 0.75rem;
          }

          .attendance-location-page .card-header,
          .attendance-location-page .card-body {
              padding-left: 1rem;
              padding-right: 1rem;
          }

          .attendance-location-page .employee-track-header {
              align-items: flex-start;
          }

          .attendance-location-page .employee-meta,
          .attendance-location-page .track-grid,
          .attendance-location-page .event-list {
              grid-template-columns: 1fr;
          }

          .attendance-location-page .filters-row > * {
              width: 100%;
          }
      }
    </style>
  @endsection

  @include('_partials/_alerts/alert-general')

  <div class="attendance-location-page">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <h5 class="mb-1">تتبع الموظفين</h5>
          <small class="text-muted">متابعة أماكن فتح السيستم وبصمة الدخول والخروج لكل موظف حسب التاريخ.</small>
        </div>
        <div class="badge bg-label-primary">{{ $stats['records'] }} سجل</div>
      </div>
      <div class="card-body">
        <div class="row g-3 align-items-end filters-row">
          <div class="col-lg-3 col-md-6">
            <label class="form-label">الموظف</label>
            <select wire:model.live="employeeId" class="form-select">
              <option value="">كل الموظفين</option>
              @foreach($employees as $employee)
                <option value="{{ $employee->id }}">{{ $employee->full_name ?: $employee->first_name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-lg-3 col-md-6">
            <label class="form-label">من تاريخ</label>
            <input wire:model.live="fromDate" type="date" dir="ltr" lang="en" class="form-control">
          </div>
          <div class="col-lg-3 col-md-6">
            <label class="form-label">إلى تاريخ</label>
            <input wire:model.live="toDate" type="date" dir="ltr" lang="en" class="form-control">
          </div>
          <div class="col-lg-3 col-md-6 d-flex gap-2">
            <button wire:click="resetToToday" type="button" class="btn btn-outline-primary w-100">اليوم</button>
            <button wire:click="resetToThisMonth" type="button" class="btn btn-primary w-100">هذا الشهر</button>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-3 col-6">
        <div class="summary-card">
          <div class="small text-muted">كل السجلات</div>
          <div class="summary-value text-primary">{{ $stats['records'] }}</div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="summary-card">
          <div class="small text-muted">كل فتح السيستم</div>
          <div class="summary-value text-info">{{ $stats['system_open_events'] }}</div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="summary-card">
          <div class="small text-muted">بصمة الدخول</div>
          <div class="summary-value text-success">{{ $stats['check_in'] }}</div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="summary-card">
          <div class="small text-muted">بصمة الخروج</div>
          <div class="summary-value text-warning">{{ $stats['check_out'] }}</div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h5 class="mb-1">سجل فتح السيستم التفصيلي</h5>
          <small class="text-muted">كل مرة تم فيها فتح السيستم مع المكان للموظفين.</small>
        </div>
        <span class="badge bg-label-info">{{ $stats['system_open_events'] }} عملية</span>
      </div>
      <div class="card-body">
        <div class="event-list">
          @forelse(($systemOpenEvents ?? collect()) as $event)
            @php
              $eventEmployee = $event->employee;
              $eventUser = $event->user;
              $displayName = $eventEmployee?->full_name ?: ($eventUser?->name ?? '---');
              $avatarUrl = $eventEmployee
                  ? route('employee-profile-photo', ['employee' => $eventEmployee->id, 'v' => optional($eventEmployee->updated_at)->timestamp])
                  : asset('assets/img/avatars/1.png');
            @endphp
            <div class="system-open-event">
              <div class="d-flex justify-content-between align-items-start gap-3">
                <div class="event-user">
                  <img src="{{ $avatarUrl }}" class="event-avatar rounded-circle" alt="Avatar">
                  <div>
                    <div class="fw-bold">{{ $displayName }}</div>
                    <div class="d-flex flex-wrap gap-2 mt-1">
                      @if($event->employee_id)
                        <span class="badge bg-label-secondary">Employee #{{ $event->employee_id }}</span>
                      @endif
                      <span class="badge bg-label-primary">User #{{ $event->user_id }}</span>
                    </div>
                  </div>
                </div>
                <div class="text-end">
                  <div class="fw-bold date-text">{{ optional($event->occurred_at)->format('Y-m-d') }}</div>
                  <small class="text-muted">{{ optional($event->occurred_at)->format('H:i:s') }}</small>
                </div>
              </div>
              <div class="mt-3">
                <div class="small text-muted mb-1">مكان فتح السيستم</div>
                <div class="coord text-info">{{ $event->latitude }}, {{ $event->longitude }}</div>
                @if($event->accuracy)
                  <div class="small text-muted mt-1">الدقة التقريبية: ± {{ number_format((float) $event->accuracy, 0) }} متر</div>
                @endif
              </div>
              <div class="d-flex flex-wrap gap-2 mt-3">
                <a
                  href="https://www.google.com/maps?q={{ $event->latitude }},{{ $event->longitude }}"
                  target="_blank"
                  class="btn btn-sm btn-label-primary"
                >
                  <i class="ti ti-map-pin me-1"></i>فتح الخريطة
                </a>
                @if($event->ip_address)
                  <span class="badge bg-label-secondary">{{ $event->ip_address }}</span>
                @endif
              </div>
            </div>
          @empty
            <div class="tracking-empty">
              لا توجد عمليات فتح سيستم مسجلة في هذه الفترة.
            </div>
          @endforelse
        </div>
      </div>
    </div>

    <div class="d-flex flex-column gap-3">
      @forelse($records as $record)
        @php
          $actions = [
              [
                  'title' => 'فتح السيستم',
                  'time' => $record->system_open_at ? \Carbon\Carbon::parse($record->system_open_at)->format('H:i') : null,
                  'lat' => $record->system_open_latitude,
                  'lng' => $record->system_open_longitude,
                  'accuracy' => $record->system_open_accuracy,
                  'class' => 'text-info',
                  'icon' => 'ti-device-desktop',
                  'tone' => 'info',
              ],
              [
                  'title' => 'بصمة الدخول',
                  'time' => $record->check_in ?: null,
                  'lat' => $record->check_in_latitude,
                  'lng' => $record->check_in_longitude,
                  'accuracy' => $record->check_in_accuracy,
                  'class' => 'text-success',
                  'icon' => 'ti-login-2',
                  'tone' => 'success',
              ],
              [
                  'title' => 'بصمة الخروج',
                  'time' => $record->check_out ?: null,
                  'lat' => $record->check_out_latitude,
                  'lng' => $record->check_out_longitude,
                  'accuracy' => $record->check_out_accuracy,
                  'class' => 'text-warning',
                  'icon' => 'ti-logout-2',
                  'tone' => 'warning',
              ],
          ];
          $locatedCount = collect($actions)->filter(fn ($action) => $action['lat'] && $action['lng'])->count();
        @endphp

        <div class="employee-track-card">
          <div class="employee-track-header">
            <div class="d-flex align-items-center gap-3">
              <img
                src="{{ $record->employee ? route('employee-profile-photo', ['employee' => $record->employee->id, 'v' => optional($record->employee->updated_at)->timestamp]) : asset('assets/img/avatars/1.png') }}"
                class="employee-avatar rounded-circle"
                alt="Avatar"
              >
              <div>
                <div class="h5 mb-1">{{ $record->employee?->full_name ?? '---' }}</div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                  <span class="badge bg-label-secondary">#{{ $record->employee_id }}</span>
                  <span class="badge bg-label-primary date-text">{{ $record->date }}</span>
                </div>
              </div>
            </div>
            <div class="text-end">
              <div class="small text-muted">المواقع المسجلة</div>
              <div class="fw-bold text-primary">{{ $locatedCount }} / 3</div>
            </div>
          </div>

          <div class="employee-meta">
            <div class="employee-meta-item">
              <div class="small text-muted">فتح السيستم</div>
              <div class="fw-bold text-info">{{ $record->system_open_at ? \Carbon\Carbon::parse($record->system_open_at)->format('H:i') : '---' }}</div>
            </div>
            <div class="employee-meta-item">
              <div class="small text-muted">بصمة الدخول</div>
              <div class="fw-bold text-success">{{ $record->check_in ?: '---' }}</div>
            </div>
            <div class="employee-meta-item">
              <div class="small text-muted">بصمة الخروج</div>
              <div class="fw-bold text-warning">{{ $record->check_out ?: '---' }}</div>
            </div>
          </div>

          <div class="track-grid">
            @foreach($actions as $action)
              <div class="track-step {{ $action['lat'] && $action['lng'] ? '' : 'is-missing' }}">
                <div class="track-step-header">
                  <div class="d-flex align-items-center gap-2">
                    <span class="track-icon text-{{ $action['tone'] }}">
                      <i class="ti {{ $action['icon'] }}"></i>
                    </span>
                    <div>
                      <div class="track-title">{{ $action['title'] }}</div>
                      <div class="small text-muted">وقت الحركة</div>
                    </div>
                  </div>
                  <span class="badge bg-label-{{ $action['tone'] }} track-time">{{ $action['time'] ?: '---' }}</span>
                </div>

                @if($action['lat'] && $action['lng'])
                  <div>
                    <div class="small text-muted mb-1">الإحداثيات</div>
                    <div class="coord {{ $action['class'] }}">{{ $action['lat'] }}, {{ $action['lng'] }}</div>
                  </div>
                  @if($action['accuracy'])
                    <div class="small text-muted">الدقة التقريبية: ± {{ number_format((float) $action['accuracy'], 0) }} متر</div>
                  @endif
                  <a
                    href="https://www.google.com/maps?q={{ $action['lat'] }},{{ $action['lng'] }}"
                    target="_blank"
                    class="btn btn-sm btn-label-primary map-btn"
                  >
                    <i class="ti ti-map-pin me-1"></i>فتح الخريطة
                  </a>
                @else
                  <div class="tracking-empty mt-2">
                    <i class="ti ti-map-off d-block mb-2"></i>
                    لا يوجد موقع مسجل لهذه الحركة.
                  </div>
                @endif
              </div>
            @endforeach
          </div>
        </div>
      @empty
        <div class="card">
          <div class="card-body text-center text-muted py-5">
            لا توجد مواقع بصمات محفوظة في هذه الفترة.
          </div>
        </div>
      @endforelse
    </div>
  </div>
</div>
