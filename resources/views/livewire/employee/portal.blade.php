<div>
  @php
    $configData = Helper::appClasses();
  @endphp

  @section('title', __('ui.employee_portal'))

  @section('page-style')
    <style>
      .employee-portal .card {
          height: 100%;
      }

      .employee-hero {
          background:
            radial-gradient(circle at top left, rgba(115, 103, 240, 0.35), transparent 32%),
            linear-gradient(135deg, rgba(115, 103, 240, 0.2), rgba(40, 199, 111, 0.12));
          border: 1px solid rgba(115, 103, 240, 0.25);
      }

      .employee-avatar {
          width: 72px;
          height: 72px;
          object-fit: cover;
      }

      .employee-portal .mini-stat {
          border: 1px solid rgba(var(--bs-border-color-rgb), 0.4);
          border-radius: 1rem;
          padding: 1rem;
          background: rgba(var(--bs-body-bg-rgb), 0.55);
      }

      .employee-portal .timeline-list {
          display: flex;
          flex-direction: column;
          gap: 0.9rem;
          max-height: 420px;
          overflow: auto;
      }

      .employee-portal .timeline-item {
          border: 1px solid rgba(var(--bs-border-color-rgb), 0.35);
          border-radius: 1rem;
          padding: 0.9rem 1rem;
          background: rgba(var(--bs-body-bg-rgb), 0.45);
      }

      .employee-portal .empty-card {
          min-height: 220px;
      }

      .employee-portal .work-timer-box {
          border: 1px solid rgba(40, 199, 111, 0.35);
          border-radius: 1rem;
          background: rgba(40, 199, 111, 0.08);
          padding: 0.85rem 1rem;
          min-width: 220px;
          white-space: normal;
          overflow-wrap: anywhere;
      }

      .employee-portal .work-timer-box .checkout-success-text {
          display: block;
          font-size: 0.9rem;
          line-height: 1.45;
          margin-bottom: 0.35rem;
      }

      .employee-portal .checkout-confirm-overlay {
          position: fixed;
          inset: 0;
          background: rgba(16, 18, 27, 0.62);
          backdrop-filter: blur(6px);
          z-index: 1055;
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 1.5rem;
      }

      .employee-portal .checkout-confirm-card {
          width: min(100%, 460px);
          border-radius: 1.4rem;
          border: 1px solid rgba(115, 103, 240, 0.28);
          background:
            radial-gradient(circle at top right, rgba(115, 103, 240, 0.18), transparent 35%),
            #2b2c42;
          box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
          overflow: hidden;
      }

      .employee-portal .checkout-confirm-card .confirm-header {
          padding: 1.15rem 1.25rem 0.5rem;
      }

      .employee-portal .checkout-confirm-card .confirm-icon {
          width: 52px;
          height: 52px;
          border-radius: 1rem;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          background: rgba(255, 159, 67, 0.16);
          color: #ff9f43;
          font-size: 1.4rem;
      }

      .employee-portal .checkout-confirm-card .confirm-body {
          padding: 0 1.25rem 1.1rem;
      }

      .employee-portal .checkout-confirm-card .confirm-actions {
          padding: 0 1.25rem 1.25rem;
          display: flex;
          gap: 0.75rem;
          justify-content: flex-end;
      }

      .employee-portal .request-form textarea {
          min-height: 120px;
          resize: vertical;
      }

      .employee-portal .request-type-tabs {
          display: grid;
          grid-template-columns: repeat(2, minmax(0, 1fr));
          gap: 0.75rem;
      }

      .employee-portal .request-type-tab {
          border: 1px solid rgba(var(--bs-border-color-rgb), 0.45);
          border-radius: 0.75rem;
          background: rgba(var(--bs-body-bg-rgb), 0.45);
          color: var(--bs-body-color);
          padding: 0.85rem 1rem;
          text-align: center;
      }

      .employee-portal .request-type-tab.active {
          border-color: var(--bs-primary);
          background: rgba(var(--bs-primary-rgb), 0.16);
          color: var(--bs-primary);
          font-weight: 700;
      }

      .employee-portal .stat-value {
          font-size: 1.8rem;
          font-weight: 700;
      }

      .employee-portal .leave-notice {
          border: 1px solid rgba(40, 199, 111, 0.3);
          border-radius: 1rem;
          background: linear-gradient(135deg, rgba(40, 199, 111, 0.22), rgba(7, 142, 81, 0.14));
          padding: 0.9rem 1rem;
          position: relative;
          overflow: hidden;
      }

      .employee-portal .leave-notice::before {
          content: "";
          position: absolute;
          inset-inline-start: 0;
          inset-block: 0;
          width: 6px;
          background: #28c76f;
      }

      .employee-portal .leave-notice-badge {
          display: inline-flex;
          align-items: center;
          gap: 0.35rem;
          padding: 0.3rem 0.65rem;
          border-radius: 999px;
          background: rgba(255, 255, 255, 0.12);
          color: #d9ffe8;
          font-size: 0.78rem;
      }

      .employee-portal .leave-countdown {
          font-size: 1.25rem;
          font-weight: 700;
          color: #b7ffd2;
          letter-spacing: 0.04em;
      }

      .employee-portal .leave-reminder-banner {
          border: 1px solid rgba(115, 103, 240, 0.28);
          border-radius: 1rem;
          background: linear-gradient(135deg, rgba(115, 103, 240, 0.18), rgba(40, 199, 111, 0.12));
      }

      @media (max-width: 767.98px) {
          .employee-portal {
              margin-inline: -0.75rem;
          }

          .employee-portal .row.g-4 {
              --bs-gutter-x: 0.75rem;
              --bs-gutter-y: 0.75rem;
          }

          .employee-portal .employee-hero {
              border-radius: 0.5rem;
          }

          .employee-portal .employee-hero .card-body {
              padding: 1rem !important;
          }

          .employee-portal .employee-hero .d-flex.flex-column.flex-lg-row {
              gap: 1rem !important;
          }

          .employee-portal .employee-hero .d-flex.align-items-center.gap-3 {
              width: 100%;
              align-items: center !important;
          }

          .employee-avatar {
              width: 58px;
              height: 58px;
          }

          .employee-portal .employee-hero h3 {
              font-size: 1.35rem;
              line-height: 1.35;
              margin-bottom: 0.25rem !important;
          }

          .employee-portal .employee-hero [x-data] {
              width: 100%;
              display: grid !important;
              grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
              align-items: stretch;
              justify-content: stretch !important;
              gap: 0.6rem !important;
          }

          .employee-portal .employee-hero [x-data] > .btn,
          .employee-portal .employee-hero [x-data] > .work-timer-box {
              width: 100%;
              min-width: 0;
          }

          .employee-portal .employee-hero [x-data] > .btn {
              min-height: 74px;
              white-space: normal;
          }

          .employee-portal .work-timer-box {
              padding: 0.65rem;
              min-width: 0;
          }

          .employee-portal .work-timer-box h4 {
              font-size: 1.25rem;
          }

          .employee-portal > .row.g-4 > .col-xl-3.col-md-6 {
              width: 50%;
              flex: 0 0 auto;
          }

          .employee-portal .mini-stat {
              min-height: 124px;
              padding: 0.8rem;
              text-align: center;
          }

          .employee-portal .stat-value {
              font-size: 1.35rem;
              line-height: 1.25;
              overflow-wrap: anywhere;
          }

          .employee-portal .mini-stat .small,
          .employee-portal .mini-stat .text-muted {
              font-size: 0.78rem;
          }

          .employee-portal .timeline-list {
              max-height: 320px;
          }
      }

      @media (max-width: 380px) {
          .employee-portal .employee-hero [x-data] {
              grid-template-columns: 1fr;
          }

          .employee-portal > .row.g-4 > .col-xl-3.col-md-6 {
              width: 100%;
          }
      }
    </style>
  @endsection

  @include('_partials/_alerts/alert-general')

  <div class="employee-portal">
    @php
      $activeLeaveNotice = $leaveNotice['active'] ?? null;
      $upcomingLeaveNotice = $leaveNotice['upcoming'] ?? null;
    @endphp

    @if($upcomingLeaveNotice)
      <div class="card leave-reminder-banner mb-4">
        <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
          <div>
            <div class="d-flex align-items-center gap-2 mb-1">
              <span class="badge bg-label-primary">{{ __('Upcoming leave') }}</span>
              <small class="text-muted">{{ $upcomingLeaveNotice['name'] }}</small>
            </div>
            <h5 class="mb-1">{{ __('Time until your leave starts') }}</h5>
            <div class="text-muted">{{ $upcomingLeaveNotice['window_label'] }}</div>
          </div>
          <div class="text-lg-end">
            <small class="d-block text-muted mb-1">{{ __('Countdown to your leave') }}</small>
            <div class="leave-countdown" data-target-ms="{{ $upcomingLeaveNotice['target_at_ms'] }}">00:00:00</div>
          </div>
        </div>
      </div>
    @endif

    <div class="row g-4">
      <div class="col-12">
        <div wire:poll.30s class="card employee-hero">
          <div class="card-body p-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
              <div class="d-flex align-items-center gap-3">
                <img
                  src="{{ route('employee-profile-photo', ['employee' => $employee->id, 'v' => optional($employee->updated_at)->timestamp]) }}"
                  alt="Avatar"
                  class="employee-avatar rounded-circle shadow-sm"
                >
                <div>
                  <h3 class="mb-1">{{ $employee->full_name }}</h3>
                  <div class="text-muted">{{ $employee->current_position }}</div>
                  <div class="small text-muted mt-1">{{ __('Employee ID') }}: {{ $employee->id }}</div>
                </div>
              </div>
              @php
                $currentLeaveNotice = $activeLeaveNotice;
                $leaveCountdownLabel = __('Time remaining');
              @endphp
              @if($currentLeaveNotice)
                <div class="leave-notice text-wrap flex-grow-1">
                  <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <span class="leave-notice-badge">
                      <i class="ti ti-calendar-event"></i>{{ $currentLeaveNotice['mode_badge'] }}
                    </span>
                    <small class="text-light">{{ $currentLeaveNotice['mode_label'] }}</small>
                  </div>
                  <div class="fw-semibold fs-5 text-white">{{ $currentLeaveNotice['name'] }}</div>
                  <div class="small text-light opacity-75 mt-1">{{ $currentLeaveNotice['window_label'] }}</div>
                  <div class="mt-3">
                    <small class="d-block text-light opacity-75 mb-1">{{ $leaveCountdownLabel }}</small>
                    <div class="leave-countdown" data-target-ms="{{ $currentLeaveNotice['target_at_ms'] }}">00:00:00</div>
                  </div>
                </div>
              @endif
              @if($this->canUseSelfFingerprint())
              <div x-data="{ confirmCheckout: false }" class="d-flex flex-wrap gap-2">
                @if (! $todayFingerprint?->check_in)
                  <button x-on:click="portalRequireAttendanceLocation().then(payload => $wire.clockIn(payload))" type="button" class="btn btn-success">
                    <i class="ti ti-login-2 me-1"></i>{{ __('Check In') }}
                  </button>
                @endif

                @if ($todayFingerprint?->check_in && ! $todayFingerprint?->check_out)
                  <div class="work-timer-box text-center">
                    <small class="d-block text-muted mb-1">{{ __('ui.total_worked_today') }}</small>
                    <h4 id="portal-work-duration" class="mb-0 text-success" data-check-in="{{ $todayFingerprint->check_in }}">00:00:00</h4>
                  </div>

                  <button
                    x-on:click="confirmCheckout = true"
                    type="button"
                    class="btn btn-warning"
                  >
                    <i class="ti ti-logout-2 me-1"></i>{{ __('Check Out') }}
                  </button>
                @elseif ($todayFingerprint?->check_out)
                  @php
                    $workedSeconds = $todayFingerprint?->check_in && $todayFingerprint?->check_out
                      ? \Carbon\Carbon::parse($todayFingerprint->check_in)->diffInSeconds(\Carbon\Carbon::parse($todayFingerprint->check_out))
                      : 0;
                    $workedDuration = sprintf('%02d:%02d:%02d', floor($workedSeconds / 3600), floor(($workedSeconds % 3600) / 60), $workedSeconds % 60);
                  @endphp
                  <div class="work-timer-box text-center">
                    <small class="d-block text-muted mb-1">{{ __('ui.workday_completed') }}</small>
                    <span class="checkout-success-text text-warning">{{ __('ui.checkout_recorded_successfully') }}</span>
                    <small class="d-block text-muted">{{ __('ui.total_worked_today') }}</small>
                    <strong class="d-block text-success mt-1">{{ $workedDuration }}</strong>
                  </div>
                @endif

                <div x-show="confirmCheckout" x-cloak class="checkout-confirm-overlay">
                  <div @click.outside="confirmCheckout = false" class="checkout-confirm-card">
                    <div class="confirm-header d-flex align-items-center gap-3">
                      <div class="confirm-icon">
                        <i class="ti ti-logout-2"></i>
                      </div>
                      <div>
                        <h5 class="mb-1">{{ __('ui.confirm_checkout') }}</h5>
                        <small class="text-muted">{{ __('ui.final_step_before_checkout') }}</small>
                      </div>
                    </div>
                    <div class="confirm-body">
                      <p class="mb-0 fs-5">{{ __('ui.checkout_confirmation_question') }}</p>
                    </div>
                    <div class="confirm-actions">
                      <button x-on:click="confirmCheckout = false" type="button" class="btn btn-label-secondary">
                        {{ __('ui.cancel') }}
                      </button>
                      <button
                        x-on:click="confirmCheckout = false; portalRequireAttendanceLocation().then(payload => $wire.clockOut(payload))"
                        type="button"
                        class="btn btn-warning"
                      >
                        <i class="ti ti-check me-1"></i>{{ __('ui.yes_end_work') }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              @endif
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="mini-stat">
          @if($activeLeaveNotice)
            <div class="text-muted small mb-2">{{ __('Leave today') }}</div>
            <div class="h5 mb-1">{{ $activeLeaveNotice['name'] }}</div>
            <div class="small">{{ __('Ends at') }}: {{ \Carbon\Carbon::parse($activeLeaveNotice['end_at_iso'])->translatedFormat('Y-m-d h:i A') }}</div>
            <div class="small text-warning mt-1">{{ __('Fingerprint is hidden during leave.') }}</div>
          @else
            <div class="text-muted small mb-2">{{ __('Today Attendance') }}</div>
            <div class="h5 mb-1">{{ $todayFingerprint?->date ?? __('Not recorded yet') }}</div>
            <div class="small">{{ __('Check In') }}: {{ $todayFingerprint?->check_in ? \Carbon\Carbon::parse($todayFingerprint->check_in)->format('h:i A') : '---' }}</div>
            <div class="small">{{ __('Check Out') }}: {{ $todayFingerprint?->check_out ? \Carbon\Carbon::parse($todayFingerprint->check_out)->format('h:i A') : '---' }}</div>
          @endif
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="mini-stat">
          <div class="text-muted small mb-2">{{ __('ui.total_salary_package') }}</div>
          <div class="stat-value mb-1">{{ number_format($salarySummary['total'], 2) }}</div>
          <div class="small text-muted">{{ __('ui.salary_with_allowances') }}</div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="mini-stat">
          <div class="text-muted small mb-2">الراتب بعد الخصم</div>
          <div class="stat-value mb-1 text-success">{{ number_format($salarySummary['net'], 2) }}</div>
          <div class="small text-muted">المستحق نهاية الشهر</div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="mini-stat">
          <div class="text-muted small mb-2">خصومات هذا الشهر</div>
          <div class="stat-value mb-1">{{ $discountSummary['cash_total'] }}</div>
          <div class="small text-muted">{{ __('ui.discount_records_count') }}: {{ $discountSummary['count'] }}</div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="mini-stat">
          <div class="text-muted small mb-2">أيام العمل هذا الشهر</div>
          <div class="stat-value mb-1">{{ $attendanceSummary['work_days'] }}</div>
          <div class="small text-muted">حسب أيام تسجيل الدخول</div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="mini-stat">
          <div class="text-muted small mb-2">ساعات العمل هذا الشهر</div>
          <div class="stat-value mb-1">{{ $attendanceSummary['worked_duration'] }}</div>
          <div class="small text-muted">من الدخول إلى الخروج</div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="mini-stat">
          <div class="text-muted small mb-2">{{ __('ui.absence_count') }}</div>
          <div class="stat-value mb-1">{{ $discountSummary['absence_count'] }}</div>
          <div class="small text-muted">هذا الشهر</div>
        </div>
      </div>

      <div class="col-xl-6">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">{{ __('ui.latest_discounts') }}</h5>
            <small class="text-muted">مبلغ الخصم وسببه</small>
          </div>
          <div class="card-body">
            @if ($latestDiscounts->count())
              <div class="timeline-list">
                @foreach ($latestDiscounts as $discount)
                  <div class="timeline-item">
                    <div class="d-flex justify-content-between gap-2">
                      <div>
                        <div class="fw-semibold">سبب الخصم</div>
                        <div class="text-muted mt-1" style="white-space: pre-wrap;">{{ $discount->reason }}</div>
                      </div>
                      <span class="badge bg-label-danger align-self-start">{{ $discount->rate }}</span>
                    </div>
                    <div class="small text-muted mt-2">{{ $discount->date }}</div>
                  </div>
                @endforeach
              </div>
            @else
              <div class="empty-card d-flex align-items-center justify-content-center text-muted">
                {{ __('ui.no_discounts_yet') }}
              </div>
            @endif
          </div>
        </div>
      </div>

      <div class="col-xl-6">
        <div class="card request-form">
          <div class="card-header">
            <h5 class="mb-0">التواصل مع الإدارة</h5>
            <small class="text-muted">اختر نوع الطلب ثم املأ البيانات المطلوبة</small>
          </div>
          <div class="card-body">
            <div class="request-type-tabs mb-4">
              <button
                wire:click="$set('requestType', 'advance')"
                type="button"
                class="request-type-tab {{ $requestType === 'advance' ? 'active' : '' }}"
              >
                {{ __('ui.request_advance') }}
              </button>
              <button
                wire:click="$set('requestType', 'message')"
                type="button"
                class="request-type-tab {{ $requestType === 'message' ? 'active' : '' }}"
              >
                {{ __('ui.message_administration') }}
              </button>
            </div>

            @if($requestType === 'advance')
              <div class="mb-3">
                <label class="form-label">{{ __('ui.amount') }}</label>
                <input wire:model.defer="advanceInfo.amount" type="number" min="1" step="0.01" class="form-control @error('advanceInfo.amount') is-invalid @enderror">
                @error('advanceInfo.amount')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="mb-3">
                <label class="form-label">{{ __('ui.note') }}</label>
                <textarea wire:model.defer="advanceInfo.note" class="form-control @error('advanceInfo.note') is-invalid @enderror"></textarea>
                @error('advanceInfo.note')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <button wire:click="submitAdvanceRequest" type="button" class="btn btn-primary">
                {{ __('ui.send_advance_request') }}
              </button>
            @else
              <div class="mb-3">
                <label class="form-label">{{ __('ui.title') }}</label>
                <input wire:model.defer="adminMessage.title" type="text" class="form-control @error('adminMessage.title') is-invalid @enderror">
                @error('adminMessage.title')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <div class="mb-3">
                <label class="form-label">{{ __('ui.message') }}</label>
                <textarea wire:model.defer="adminMessage.body" class="form-control @error('adminMessage.body') is-invalid @enderror"></textarea>
                @error('adminMessage.body')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
              <button wire:click="sendMessageToAdministration" type="button" class="btn btn-success">
                {{ __('ui.send_message_to_administration') }}
              </button>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  @push('custom-scripts')
    <script>
      function getPortalDubaiSecondsOfDay() {
        const parts = new Intl.DateTimeFormat('en-GB', {
          timeZone: 'Asia/Dubai',
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit',
          hour12: false
        }).formatToParts(new Date());

        const values = Object.fromEntries(parts.map((part) => [part.type, part.value]));

        return (Number(values.hour || 0) * 3600) + (Number(values.minute || 0) * 60) + Number(values.second || 0);
      }

      function updatePortalWorkDuration() {
        const workDurationElement = document.getElementById('portal-work-duration');

        if (!workDurationElement) {
          return;
        }

        const checkInValue = workDurationElement.dataset.checkIn;
        if (!checkInValue) {
          return;
        }

        const [hours, minutes, seconds] = checkInValue.split(':').map(Number);
        const checkInSeconds = ((hours || 0) * 3600) + ((minutes || 0) * 60) + (seconds || 0);

        let diffSeconds = Math.max(0, getPortalDubaiSecondsOfDay() - checkInSeconds);
        const diffHours = String(Math.floor(diffSeconds / 3600)).padStart(2, '0');
        diffSeconds %= 3600;
        const diffMinutes = String(Math.floor(diffSeconds / 60)).padStart(2, '0');
        const remainingSeconds = String(diffSeconds % 60).padStart(2, '0');

        workDurationElement.textContent = `${diffHours}:${diffMinutes}:${remainingSeconds}`;
      }

      setInterval(updatePortalWorkDuration, 1000);
      updatePortalWorkDuration();

      function formatPortalLeaveCountdown(totalSeconds) {
        const isArabic = (document.documentElement.lang || '').toLowerCase().startsWith('ar');
        const days = Math.floor(totalSeconds / 86400);
        const hours = Math.floor((totalSeconds % 86400) / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        const formatUnit = (value, singularEn, pluralEn, arabicLabel) => {
          if (!value) {
            return null;
          }

          return isArabic
            ? `${value} ${arabicLabel}`
            : `${value} ${value === 1 ? singularEn : pluralEn}`;
        };

        const parts = [
          formatUnit(days, 'day', 'days', 'يوم'),
          formatUnit(hours, 'hour', 'hours', 'ساعة'),
          formatUnit(minutes, 'minute', 'minutes', 'دقيقة'),
          formatUnit(seconds, 'second', 'seconds', 'ثانية'),
        ].filter(Boolean);

        return parts.slice(0, 3).join(isArabic ? ' و ' : ' and ') || (isArabic ? '0 ثانية' : '0 seconds');
      }

      function updatePortalLeaveCountdowns() {
        document.querySelectorAll('.employee-portal .leave-countdown').forEach((element) => {
          const targetMs = Number(element.dataset.targetMs || 0);
          if (!targetMs) {
            return;
          }

          const diff = Math.max(0, targetMs - Date.now());
          const totalSeconds = Math.floor(diff / 1000);

          element.textContent = formatPortalLeaveCountdown(totalSeconds);
        });
      }

      setInterval(updatePortalLeaveCountdowns, 1000);
      updatePortalLeaveCountdowns();

      let portalLocationWarningShownAt = 0;

      function portalCachedAttendanceLocation() {
        const cached = window.__lastAttendanceLocation || JSON.parse(sessionStorage.getItem('attendance-location-cache') || 'null');

        if (!cached || !cached.latitude || !cached.longitude || !cached.capturedAt) {
          return null;
        }

        if (Date.now() - Number(cached.capturedAt) > 10 * 60 * 1000) {
          return null;
        }

        return {
          latitude: cached.latitude,
          longitude: cached.longitude,
          accuracy: cached.accuracy
        };
      }

      function portalRememberAttendanceLocation(payload) {
        const cached = {
          ...payload,
          capturedAt: Date.now()
        };

        window.__lastAttendanceLocation = cached;
        sessionStorage.setItem('attendance-location-cache', JSON.stringify(cached));
      }

      function portalAttendanceLocationPayload() {
        return new Promise((resolve, reject) => {
          const cached = portalCachedAttendanceLocation();
          if (cached) {
            resolve(cached);
            return;
          }

          if (!navigator.geolocation) {
            reject(new Error('geolocation_unavailable'));
            return;
          }

          const normalizePosition = position => ({
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy
          });

          const requestPosition = options => new Promise((positionResolve, positionReject) => {
            navigator.geolocation.getCurrentPosition(
              position => positionResolve(normalizePosition(position)),
              positionReject,
              options
            );
          });

          requestPosition({ enableHighAccuracy: false, timeout: 25000, maximumAge: 60000 })
            .then(payload => {
              portalRememberAttendanceLocation(payload);
              resolve(payload);
            })
            .catch(firstError => {
              requestPosition({ enableHighAccuracy: true, timeout: 35000, maximumAge: 0 })
                .then(payload => {
                  portalRememberAttendanceLocation(payload);
                  resolve(payload);
                })
                .catch(() => reject(firstError));
            });
        });
      }

      function portalRequireAttendanceLocation() {
        return portalAttendanceLocationPayload().catch((error) => {
          if (Date.now() - portalLocationWarningShownAt < 5000) {
            throw error;
          }

          portalLocationWarningShownAt = Date.now();

          const timedOut = error?.code === 3;
          const message = timedOut
            ? 'لم يتمكن المتصفح من تحديد موقعك الآن. افتح خرائط الهاتف لحظة للتأكد من عمل GPS، ثم ارجع واضغط تسجيل البصمة مرة أخرى.'
            : 'لم يتمكن المتصفح من قراءة موقعك الآن. تأكد أن خدمات الموقع مفعلة، ثم اضغط تسجيل البصمة مرة أخرى.';

          if (window.toastr) {
            toastr.warning(message, '', {
              closeButton: true,
              timeOut: 9000,
              extendedTimeOut: 3000,
              progressBar: true,
              positionClass: 'toast-top-center',
              rtl: true
            });
          } else {
            window.dispatchEvent(new CustomEvent('toastr', {
              detail: {
                type: 'warning',
                message
              }
            }));
          }

          throw error;
        });
      }
    </script>
  @endpush
</div>
