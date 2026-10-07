<div>

  @php
  $configData = Helper::appClasses();
  use App\Models\Employee;
  use Carbon\Carbon;
  @endphp

  @section('title', 'Dashboard')

  @section('vendor-style')

  @endsection

  @section('page-style')
  <style>
    .match-height>[class*='col'] {
      display: flex;
      flex-flow: column;
    }

    .match-height>[class*='col']>.card {
      flex: 1 1 auto;
    }

    .btn-tr {
      opacity: 0;
    }

    tr:hover .btn-tr {
      display: inline-block;
      opacity: 1;
    }

    tr:hover .td {
      color: #7367f0 !important;
    }

    .work-timer-box {
      border: 1px solid rgba(40, 199, 111, 0.35);
      border-radius: 1rem;
      background: rgba(40, 199, 111, 0.08);
      padding: 0.85rem 1rem;
      white-space: normal;
      overflow-wrap: anywhere;
    }

    .work-timer-box .checkout-success-text {
      display: block;
      font-size: 0.9rem;
      line-height: 1.45;
      margin-bottom: 0.35rem;
    }

    .checkout-confirm-overlay {
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

    .checkout-confirm-card {
      width: min(100%, 460px);
      border-radius: 1.4rem;
      border: 1px solid rgba(115, 103, 240, 0.28);
      background:
        radial-gradient(circle at top right, rgba(115, 103, 240, 0.18), transparent 35%),
        #2b2c42;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
      overflow: hidden;
    }

    .checkout-confirm-card .confirm-header {
      padding: 1.15rem 1.25rem 0.5rem;
    }

    .checkout-confirm-card .confirm-icon {
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

    .checkout-confirm-card .confirm-body {
      padding: 0 1.25rem 1.1rem;
    }

    .checkout-confirm-card .confirm-actions {
      padding: 0 1.25rem 1.25rem;
      display: flex;
      gap: 0.75rem;
      justify-content: flex-end;
    }

    .leave-notice {
      border: 1px solid rgba(40, 199, 111, 0.3);
      border-radius: 1rem;
      background: linear-gradient(135deg, rgba(40, 199, 111, 0.22), rgba(7, 142, 81, 0.14));
      padding: 0.9rem 1rem;
      position: relative;
      overflow: hidden;
    }

    .leave-notice::before {
      content: "";
      position: absolute;
      inset-inline-start: 0;
      inset-block: 0;
      width: 6px;
      background: #28c76f;
    }

    .leave-notice-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.35rem;
      padding: 0.3rem 0.65rem;
      border-radius: 999px;
      background: rgba(255, 255, 255, 0.12);
      color: #d9ffe8;
      font-size: 0.78rem;
    }

    .leave-countdown {
      font-size: 1.25rem;
      font-weight: 700;
      color: #b7ffd2;
      letter-spacing: 0.04em;
    }

    .leave-reminder-banner {
      border: 1px solid rgba(115, 103, 240, 0.28);
      border-radius: 1rem;
      background: linear-gradient(135deg, rgba(115, 103, 240, 0.18), rgba(40, 199, 111, 0.12));
    }

    .weekly-holiday-notice {
      border: 1px solid rgba(115, 103, 240, 0.36);
      border-radius: 1rem;
      background: rgba(115, 103, 240, 0.14);
      padding: 0.9rem 1rem;
      white-space: normal;
    }

    .weekly-holiday-notice .holiday-icon {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(115, 103, 240, 0.18);
      color: #a99cff;
      font-size: 1.25rem;
      flex: 0 0 auto;
    }

    .today-attendance-table {
      max-height: 260px;
      overflow: auto;
    }

    .today-attendance-table .table {
      min-width: 640px;
    }
  </style>
  @endsection

  {{-- Alerts --}}
  @include('_partials/_alerts/alert-general')

  {{-- <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">Dashboard</a>
      </li>
    </ol>
  </nav> --}}

  {{-- @if(Auth::user()->hasRole('Employee|Viewer')) --}}
  @if($managementAlert)
    <div class="alert alert-danger alert-dismissible" style="text-align: justify;" role="alert">
      <h5 class="alert-heading mb-2">{{ __('Management Alert') }}</h5>
      <p class="mb-0" style="white-space: pre-wrap;">{{ $managementAlert['body'] }}</p>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif
  {{-- @endif --}}

  @php
    $activeLeaveNotice = $leaveNotice['active'] ?? null;
    $upcomingLeaveNotice = $leaveNotice['upcoming'] ?? null;
    $weeklyHolidayToday = $weeklyHolidayNotice ?? null;
  @endphp

  @if($upcomingLeaveNotice && !Auth::user()->hasRole('Admin'))
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

  <div class="row match-height">
    <div class="col-xl-4 mb-4 col-lg-5 col-12">
      <div wire:poll.30s class="card h-100">
        <div class="card-header">
          <div class="d-flex justify-content-between mb-3">
            <div class="card-title mb-0">
              <h4 class="card-title mb-1">{{ __('Hi,') }} {{ Employee::find(Auth::user()->employee_id)->first_name }}! 👋</h4>
              <small class="text-muted">{{ __('Start your day with a smile') }}</small>
            </div>
            <small class="text-muted">{{ __('ID: ') . Auth::user()->employee_id }}</small>
          </div>
        </div>

        <div class="d-flex align-items-end row h-100">
          <div class="col-7">
            <div class="card-body text-nowrap">
              @php
                $currentLeaveNotice = $activeLeaveNotice;
                $leaveCountdownLabel = __('Time remaining');
              @endphp
              @if($currentLeaveNotice && !Auth::user()->hasRole('Admin'))
                <div class="leave-notice mb-3 text-wrap">
                  <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                    <span class="leave-notice-badge">
                      <i class="ti ti-calendar-event"></i>{{ $currentLeaveNotice['mode_badge'] }}
                    </span>
                    <small class="text-light">{{ $currentLeaveNotice['mode_label'] }}</small>
                  </div>
                  @if(!empty($currentLeaveNotice['employee_name']) && !Auth::user()->hasAnyRole(['Employee', 'ManagementEmployee', 'Viewer']))
                    <div class="small text-light opacity-75 mb-1">{{ __('Employee') }}: {{ $currentLeaveNotice['employee_name'] }}</div>
                  @endif
                  <div class="fw-semibold fs-5 text-white">{{ $currentLeaveNotice['name'] }}</div>
                  <div class="small text-light opacity-75 mt-1">{{ $currentLeaveNotice['window_label'] }}</div>
                  <div class="mt-3">
                    <small class="d-block text-light opacity-75 mb-1">{{ $leaveCountdownLabel }}</small>
                    <div class="leave-countdown" data-target-ms="{{ $currentLeaveNotice['target_at_ms'] }}">00:00:00</div>
                  </div>
                </div>
              @endif
              {{-- <h5 class="card-title mb-0">{{ __('Hi,') }} {{ Employee::find(Auth::user()->employee_id)->first_name
                }}! 👋</h5>
              <p class="mb-2">{{ __('Start your day with a smile') }}</p> --}}
              {{-- <h5 wire:poll.60s class="text-primary mt-3 mb-2">{{ now()->format('Y/m/d - H:i') }}</h5> --}}
              <h5 id="date" class="text-primary mt-3 mb-1"></h5>
              <h5 id="time" class="text-primary mb-2"></h5>
              @if($weeklyHolidayToday)
                <div class="weekly-holiday-notice mt-3">
                  <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="holiday-icon">
                      <i class="ti ti-calendar-time"></i>
                    </span>
                    <div>
                      <div class="fw-semibold text-white">اليوم إجازتك الأسبوعية</div>
                      <small class="text-muted">{{ $weeklyHolidayToday['day_name'] }} هو يوم إجازتك، لذلك تم إيقاف البصمة لهذا اليوم.</small>
                    </div>
                  </div>
                  <small class="d-block text-muted mb-1">تعود البصمة مع بداية اليوم الجديد</small>
                  <div class="leave-countdown" data-target-ms="{{ $weeklyHolidayToday['target_at_ms'] }}">00:00:00</div>
                </div>
              @elseif($this->canUseSelfFingerprint())
              <div x-data="{ confirmCheckout: false }" class="d-flex flex-column gap-2 mt-3">
                @if (! $todayFingerprint?->check_in)
                  <button
                    x-on:click="attendanceActionPayload().then(payload => $wire.clockIn(payload))"
                    type="button"
                    class="btn btn-success"
                  >
                    <i class="ti ti-login-2 ti-xs me-1"></i>{{ __('Check In') }}
                  </button>
                @endif

                @if ($todayFingerprint?->check_in && ! $todayFingerprint?->check_out)
                  <div class="work-timer-box text-center">
                    <small class="d-block text-muted mb-1">{{ __('ui.total_worked_today') }}</small>
                    <h4 id="work-duration" class="mb-0 text-success" data-check-in="{{ $todayFingerprint->check_in }}">00:00:00</h4>
                  </div>

                  <button
                    x-on:click="confirmCheckout = true"
                    type="button"
                    class="btn btn-warning"
                  >
                    <i class="ti ti-logout-2 ti-xs me-1"></i>{{ __('Check Out') }}
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
                        x-on:click="confirmCheckout = false; attendanceActionPayload().then(payload => $wire.clockOut(payload))"
                        type="button"
                        class="btn btn-warning"
                      >
                        <i class="ti ti-check me-1"></i>{{ __('ui.yes_end_work') }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
                <div class="mt-3">
                  <small class="d-block text-muted">{{ __('Today Attendance') }}</small>
                  <small class="d-block">{{ __('Check In') }}: {{ $todayFingerprint?->check_in ? \Carbon\Carbon::parse($todayFingerprint->check_in)->format('h:i A') : '---' }}</small>
                  <small class="d-block">{{ __('Check Out') }}: {{ $todayFingerprint?->check_out ? \Carbon\Carbon::parse($todayFingerprint->check_out)->format('h:i A') : '---' }}</small>
                </div>
              @endif
              @if($this->canOpenCreateMenu())
              <div class="btn-group dropend">
                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown"
                  aria-haspopup="true" aria-expanded="false"><i class="ti ti-menu-2 ti-xs me-1"></i>{{ __('Add New')
                  }}</button>
                <ul class="dropdown-menu">
                  @can('create employees')
                  <li><a class="dropdown-item" href="{{ route('structure-employees') }}"><i
                        class="ti ti-menu-2 ti-xs me-1"></i> {{ __('Employee') }}</a></li>
                  <li>
                    <hr class="dropdown-divider">
                  </li>
                  @endcan
                  @if($this->canCreateAttendanceFingerprintRecord())
                  <li><a class="dropdown-item" href="{{ route('attendance-fingerprints') }}"><i
                        class="ti ti-menu-2 ti-xs me-1"></i>{{ __('Fingerprint') }}</a></li>
                  @endif
                  @if($this->canCreateAttendanceLeaves())
                  <li><a wire:click='showCreateLeaveModal' class="dropdown-item" data-bs-toggle="modal"
                      data-bs-target="#leaveModal" href=""><i class="ti ti-menu-2 ti-xs me-1"></i>{{ __('Leave') }}</a>
                  </li>
                  @endif
                </ul>
              </div>
              @endif
            </div>
          </div>
          <div class="col-5 text-center text-sm-left h-100 d-flex align-items-end">
            <div class="card-body pb-0 px-0 px-md-4 w-100">
              <img src="{{asset('assets/img/illustrations/card-advance-sale.png')}}" class="img-fluid" alt="view sales"
                style="object-fit: contain; width: 100%; height: auto;">
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-xl-8 mb-4 col-lg-7 col-12">
      <div class="card h-100">
        <div class="card-header">
          <div class="d-flex justify-content-between mb-3">
            <h5 class="card-title mb-0">{{ __('Statistics') }}</h5>
            <small class="text-muted">{{ __('Data as of: ') . ($batchDates[1] ?? __('N/A')) }}</small>
          </div>
        </div>
        @if(Auth::user()->hasRole('Admin'))
          <div class="card-body">
            <div class="row gy-3">
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded-pill bg-label-success me-3 p-2"><i class="ti ti-cash ti-sm"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">AED {{ number_format($adminStats['treasury_cash'], 2) }}</h5>
                    <small>{{ __('Treasury Cash') }}</small>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded-pill bg-label-danger me-3 p-2"><i class="ti ti-receipt-2 ti-sm"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">AED {{ number_format($adminStats['expenses'], 2) }}</h5>
                    <small>{{ __('Total Expenses') }}</small>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded-pill bg-label-warning me-3 p-2"><i class="ti ti-edit ti-sm"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ number_format($adminStats['change_operations']) }}</h5>
                    <small>{{ __('Change Operations') }}</small>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded-pill bg-label-info me-3 p-2"><i class="ti ti-messages ti-sm"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ number_format($adminStats['messages']) }}</h5>
                    <small>{{ __('Total Messages') }}</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @else
          @can('read sms')
          <div class="card-body">
            <div class="row gy-3">
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded-pill bg-label-primary me-3 p-2"><i class="ti ti-activity ti-sm"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ $accountBalance['is_active'] }}</h5>
                    <small>{{ __('API Status') }}</small>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded-pill bg-label-primary me-3 p-2"><i class="ti ti-calculator ti-sm"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ $accountBalance['balance'] }}</h5>
                    <small>{{ __('API Balance') }}</small>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded-pill bg-label-success me-3 p-2"><i class="ti ti-speakerphone ti-sm"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ $messagesStatus['sent'] }}</h5>
                    <small>{{ __('Successful SMS') }}</small>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div wire:click='sendPendingMessages' class="badge rounded-pill bg-label-danger me-3 p-2"
                    style="cursor: pointer"><i class="ti ti-send ti-sm"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ $messagesStatus['unsent'] }}</h5>
                    <small>{{ __('Pending SMS') }}</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endcan
        @endif
        @canany(['create attendance leaves', 'view attendance leaves', 'manage attendance leaves'])
        @if($showStatictics)
          <div class="card-body pt-0">
            <div class="row gy-3">
              @if(!Auth::user()->hasAnyRole(['Employee', 'ManagementEmployee', 'Viewer']))
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded-pill bg-label-primary me-3 p-2"><i class="ti ti-users ti-sm"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ count($activeEmployees) }}</h5>
                    <small>{{ __('Active Employees') }}</small>
                  </div>
                </div>
              </div>
              @endif
              {{-- <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded-pill bg-label-secondary me-3 p-2"><i class="ti ti-calendar ti-sm"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ count($leaveRecords) }}</h5>
                    <small>{{ __('Today Records') }}</small>
                  </div>
                </div>
              </div> --}}
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded-pill bg-label-warning me-3 p-2"><i class="ti ti-zzz ti-sm"></i></div>
                  <div class="card-info">
                    <h5 class="mb-0">{{ $employee->max_leave_allowed }}</h5>
                    <small>{{ __('Leaves Balance') }}</small>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded-pill bg-label-warning me-3 p-2"><i class="ti {{ Auth::user()->hasRole('Admin') ? 'ti-cash-banknote' : 'ti-alarm' }} ti-sm"></i></div>
                  <div class="card-info">
                    @if(Auth::user()->hasRole('Admin'))
                      <h5 class="mb-0">AED {{ number_format($adminStats['monthly_salaries'], 2) }}</h5>
                      <small>إجمالي الرواتب الشهرية</small>
                    @else
                      <h5 class="mb-0">{{ $employee->hourly_counter }}</h5>
                      <small>{{ __('Hourly Counter') }}</small>
                    @endif
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="d-flex align-items-center">
                  <div class="badge rounded-pill bg-label-warning me-3 p-2"><i class="ti {{ Auth::user()->hasRole('Admin') ? 'ti-wallet' : 'ti-hourglass' }} ti-sm"></i></div>
                  <div class="card-info">
                    @if(Auth::user()->hasRole('Admin'))
                      <h5 class="mb-0">AED {{ number_format($adminStats['monthly_employee_withdrawals'], 2) }}</h5>
                      <small>مسحوبات الموظفين الشهرية</small>
                    @else
                      <h5 class="mb-0">{{ $employee->delay_counter }}</h5>
                      <small>{{ __('Delay Counter') }}</small>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endif
        @endcanany
        @if($this->canViewTodayAttendanceRecords())
          @php
            $canEditTodayAttendance = $this->canEditTodayAttendance();
            $canDeleteTodayAttendance = $this->canDeleteTodayAttendance();
            $canManageTodayAttendance = $canEditTodayAttendance || $canDeleteTodayAttendance;
          @endphp
          <div class="card-body {{ $showStatictics ? 'pt-0' : '' }}">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h6 class="mb-1">حضور الموظفين اليوم</h6>
                <small class="text-muted">{{ $this->getCurrentAttendanceDate() }}</small>
              </div>
              <span class="badge bg-label-success">{{ $todayAttendanceRecords?->count() ?? 0 }}</span>
            </div>

            <div class="today-attendance-table table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead>
                  <tr>
                    <th>الموظف</th>
                    <th class="text-center">{{ __('Check In') }}</th>
                    <th class="text-center">{{ __('Check Out') }}</th>
                    <th class="text-center">الحالة</th>
                    @if($canManageTodayAttendance)
                      <th class="text-center">الإجراءات</th>
                    @endif
                  </tr>
                </thead>
                <tbody>
                  @forelse(($todayAttendanceRecords ?? collect()) as $attendance)
                    <tr>
                      <td>
                        <div class="fw-semibold">{{ $attendance->employee?->full_name ?? '---' }}</div>
                        <small class="text-muted">#{{ $attendance->employee_id }}</small>
                      </td>
                      <td class="text-center">
                        <span class="badge bg-label-success">{{ $attendance->check_in ?: '---' }}</span>
                      </td>
                      <td class="text-center">
                        <span class="badge bg-label-warning">{{ $attendance->check_out ?: '---' }}</span>
                      </td>
                      <td class="text-center">
                        @if($attendance->check_out)
                          <span class="badge bg-label-secondary">انتهى الدوام</span>
                        @else
                          <span class="badge bg-label-primary">داخل العمل</span>
                        @endif
                      </td>
                      @if($canManageTodayAttendance)
                        <td class="text-center">
                          @if($canEditTodayAttendance)
                            <button
                              wire:click="showEditAttendanceModal({{ $attendance->id }})"
                              type="button"
                              class="btn btn-sm btn-icon btn-label-info"
                              data-bs-toggle="modal"
                              data-bs-target="#attendanceEditModal"
                              title="تعديل"
                            >
                              <i class="ti ti-pencil"></i>
                            </button>
                          @endif
                          @if($canDeleteTodayAttendance)
                            <button
                              wire:click="confirmDeleteAttendance({{ $attendance->id }})"
                              type="button"
                              class="btn btn-sm btn-icon btn-label-danger"
                              title="إزالة الحضور"
                            >
                              <i class="ti ti-trash"></i>
                            </button>
                            @if($confirmedAttendanceId === $attendance->id)
                              <button wire:click="deleteAttendance" type="button" class="btn btn-xs btn-danger">
                                {{ __('Sure?') }}
                              </button>
                            @endif
                          @endif
                        </td>
                      @endif
                    </tr>
                  @empty
                    <tr>
                      <td colspan="{{ $canManageTodayAttendance ? 5 : 4 }}" class="text-center text-muted py-4">
                        لا يوجد موظفون سجلوا دخول اليوم.
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        @endif
      </div>
    </div>

    {{-- <div class="col-xl-4 col-12">
      <div class="row">
        <div class="col-xl-6 mb-4 col-md-3 col-6">
          <div class="card">
            <div class="card-header pb-0">
              <h5 class="card-title mb-0">82.5k</h5>
              <small class="text-muted">Expenses</small>
            </div>
            <div class="card-body">
              <div id="expensesChart"></div>
              <div class="mt-md-2 text-center mt-lg-3 mt-3">
                <small class="text-muted mt-3">$21k Expenses more than last month</small>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-6 mb-4 col-md-3 col-6">
          <div class="card">
            <div class="card-header pb-0">
              <h5 class="card-title mb-0">Profit</h5>
              <small class="text-muted">Last Month</small>
            </div>
            <div class="card-body">
              <div id="profitLastMonth"></div>
              <div class="d-flex justify-content-between align-items-center mt-3 gap-3">
                <h4 class="mb-0">624k</h4>
                <small class="text-success">+8.24%</small>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-12 mb-4 col-md-6">
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between">
                <div class="d-flex flex-column">
                  <div class="card-title mb-auto">
                    <h5 class="mb-1 text-nowrap">Generated Leads</h5>
                    <small>Monthly Report</small>
                  </div>
                  <div class="chart-statistics">
                    <h3 class="card-title mb-1">4,350</h3>
                    <small class="text-success text-nowrap fw-semibold"><i class='ti ti-chevron-up me-1'></i>
                      15.8%</small>
                  </div>
                </div>
                <div id="generatedLeadsChart"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div> --}}

    {{-- <div class="col-12 col-xl-8 mb-4 col-lg-7">
      <div class="card">
        <div class="card-header pb-3 ">
          <h5 class="m-0 me-2 card-title">Revenue Report</h5>
        </div>
        <div class="card-body">
          <div class="row row-bordered g-0">
            <div class="col-md-8">
              <div id="totalRevenueChart"></div>
            </div>
            <div class="col-md-4">
              <div class="text-center mt-4">
                <div class="dropdown">
                  <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="budgetId"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <script>
                      document.write(new Date().getFullYear())

                    </script>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end" aria-labelledby="budgetId">
                    <a class="dropdown-item prev-year1" href="javascript:void(0);">
                      <script>
                        document.write(new Date().getFullYear() - 1)

                      </script>
                    </a>
                    <a class="dropdown-item prev-year2" href="javascript:void(0);">
                      <script>
                        document.write(new Date().getFullYear() - 2)

                      </script>
                    </a>
                    <a class="dropdown-item prev-year3" href="javascript:void(0);">
                      <script>
                        document.write(new Date().getFullYear() - 3)

                      </script>
                    </a>
                  </div>
                </div>
              </div>
              <h3 class="text-center pt-4 mb-0">$25,825</h3>
              <p class="mb-4 text-center"><span class="fw-semibold">Budget: </span>56,800</p>
              <div class="px-3">
                <div id="budgetChart"></div>
              </div>
              <div class="text-center mt-4">
                <button type="button" class="btn btn-primary">Increase Button</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div> --}}
  </div>

  <div class="row">
    <div class="col">
      <div class="card">
        <h5 class="card-header">{{ __('Recently Leaves')}}</h5>
        <div class="table-responsive text-nowrap">
          <table class="table table-hover">
            <thead>
              <tr>
                <th class="col-1">{{ __('ID') }}</th>
                @if(!Auth::user()->hasAnyRole(['Employee', 'ManagementEmployee', 'Viewer']))
                <th>{{ __('Employee') }}</th>
                @endif
                <th class="col-1">{{ __('Type') }}</th>
                <th style="text-align: center">{{ __('Details') }}</th>
                @if($this->canEditAttendanceLeaves() || $this->canDeleteAttendanceLeaves())
                <th style="text-align: center">{{ __('Actions') }}</th>
                @endif
              </tr>
            </thead>
            <tbody class="table-border-bottom-0">
              @forelse($leaveRecords as $leave)
              <tr>
                <td><strong>{{ $leave->id }}</strong></td>
                @if(!Auth::user()->hasAnyRole(['Employee', 'ManagementEmployee', 'Viewer']))
                <td class="td">{{ $this->getEmployeeName($leave->employee_id) }}</td>
                @endif
                <td>{{ $this->getLeaveType($leave->leave_id) }}</td>
                <td style="text-align: center">
                  <span class="badge bg-label-primary mb-2 me-1" style="font-size: 14px">{{ $leave->from_date . ' --> '
                    . $leave->to_date }}</span>
                  <br>
                  @if ($leave->start_at !== null)
                  <span class="badge bg-label-secondary me-1">{{ Carbon::parse($leave->start_at)->format('H:i') . ' -->
                    ' . Carbon::parse($leave->end_at)->format('H:i') }}</span>
                  @endif
                  <br>
                  <button type="button" class="btn btn-sm btn-outline-info mt-2" title="{{ __('Duration') }}">
                    <i class="ti ti-clock-hour-4 me-1"></i>{{ $this->getLeaveDurationLabel($leave) }}
                  </button>
                </td>
                @if($this->canEditAttendanceLeaves() || $this->canDeleteAttendanceLeaves())
                <td style="text-align: center">
                  @if($this->canEditAttendanceLeaves())
                  <button type="button"
                    class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-secondary waves-effect">
                    <span wire:click.prevent="showEditLeaveModal({{ $leave->id }})" data-bs-toggle="modal"
                      data-bs-target="#leaveModal" class="ti ti-pencil"></span>
                  </button>
                  @endif
                  @if($this->canDeleteAttendanceLeaves())
                  <button type="button" class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-danger waves-effect">
                    <span wire:click.prevent="confirmDestroyLeave({{ $leave->id }})" class="ti ti-trash"></span>
                  </button>
                  @if ($confirmedId === $leave->id)
                  <button wire:click.prevent="destroyLeave" type="button"
                    class="btn btn-xs btn-danger waves-effect waves-light">
                    {{ __('Sure?') }}
                  </button>
                  @endif
                  @endif
                </td>
                @endif
              </tr>
              @empty
              <tr>
                <td colspan="6">
                  <div class="mt-2 mb-2" style="text-align: center">
                    <h3 class="mb-1 mx-2">{{ __('Excellent!') .  '🎉' }}</h3>
                    <p class="mb-4 mx-2">
                      {{ __('No leaves found, keep up the good work.') }}
                    </p>
                    @if($this->canCreateAttendanceLeaves())
                    <button class="btn btn-label-primary mb-4" data-bs-toggle="modal" data-bs-target="#leaveModal">
                      {{ __('Add New Leave') }}
                    </button>
                    @endif
                    <div>
                      <img src="{{ asset('assets/img/illustrations/page-misc-under-maintenance.png') }}" width="200"
                        class="img-fluid">
                    </div>
                  </div>
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- Modals --}}
  <div wire:ignore.self class="modal fade" id="attendanceEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">تعديل الحضور</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">{{ __('Date') }}</label>
            <input wire:model.defer="attendanceForm.date" type="date" class="form-control @error('attendanceForm.date') is-invalid @enderror">
            @error('attendanceForm.date')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">{{ __('Check In') }}</label>
            <input wire:model.defer="attendanceForm.checkIn" type="time" class="form-control @error('attendanceForm.checkIn') is-invalid @enderror">
            @error('attendanceForm.checkIn')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="mb-0">
            <label class="form-label">{{ __('Check Out') }}</label>
            <input wire:model.defer="attendanceForm.checkOut" type="time" class="form-control @error('attendanceForm.checkOut') is-invalid @enderror">
            @error('attendanceForm.checkOut')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
          <button wire:click="updateAttendance" type="button" class="btn btn-primary">حفظ التعديل</button>
        </div>
      </div>
    </div>
  </div>

  @include('_partials/_modals/modal-leaveWithEmployee')

  @push('custom-scripts')
  <script>
    function updateClock() {
            const now = new Date();
            const locale = document.documentElement.lang === 'ar' ? 'ar-EG' : 'en-US';
            const dateOptions = {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', timeZone: 'Asia/Dubai'
            };
            const timeOptions = {
                hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true, timeZone: 'Asia/Dubai'
            };

            const formattedDate = now.toLocaleDateString(locale, dateOptions);
            const formattedTime = now.toLocaleTimeString(locale, timeOptions);

            document.getElementById('date').innerHTML = formattedDate;
            document.getElementById('time').innerHTML = formattedTime;
        }

        setInterval(updateClock, 1000); // Update every second
        updateClock(); // Initial call to display clock immediately
  </script>
  <script>
    function getDubaiSecondsOfDay() {
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

    function updateWorkDuration() {
      const workDurationElement = document.getElementById('work-duration');

      if (!workDurationElement) {
        return;
      }

      const checkInValue = workDurationElement.dataset.checkIn;
      if (!checkInValue) {
        return;
      }

      const [hours, minutes, seconds] = checkInValue.split(':').map(Number);
      const checkInSeconds = ((hours || 0) * 3600) + ((minutes || 0) * 60) + (seconds || 0);

      let diffSeconds = getDubaiSecondsOfDay() - checkInSeconds;
      if (diffSeconds < 0) {
        diffSeconds += 86400;
      }
      diffSeconds = Math.max(0, diffSeconds);
      const diffHours = String(Math.floor(diffSeconds / 3600)).padStart(2, '0');
      diffSeconds %= 3600;
      const diffMinutes = String(Math.floor(diffSeconds / 60)).padStart(2, '0');
      const remainingSeconds = String(diffSeconds % 60).padStart(2, '0');

      workDurationElement.textContent = `${diffHours}:${diffMinutes}:${remainingSeconds}`;
    }

    setInterval(updateWorkDuration, 1000);
    updateWorkDuration();
  </script>
  <script>
    function formatLeaveCountdown(totalSeconds) {
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

    function updateLeaveCountdowns() {
      document.querySelectorAll('.leave-countdown').forEach((element) => {
        const targetMs = Number(element.dataset.targetMs || 0);
        if (!targetMs) {
          return;
        }

        const diff = Math.max(0, targetMs - Date.now());
        const totalSeconds = Math.floor(diff / 1000);

        element.textContent = formatLeaveCountdown(totalSeconds);
      });
    }

    setInterval(updateLeaveCountdowns, 1000);
    updateLeaveCountdowns();
  </script>
  <script>
    let attendanceLocationWarningShownAt = 0;
    const attendanceLocationRequired = @json($this->requiresAttendanceLocation());

    function cachedAttendanceLocation() {
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

    function rememberAttendanceLocation(payload) {
      const cached = {
        ...payload,
        capturedAt: Date.now()
      };

      window.__lastAttendanceLocation = cached;
      sessionStorage.setItem('attendance-location-cache', JSON.stringify(cached));
    }

    function attendanceLocationPayload() {
      return new Promise((resolve, reject) => {
        const cached = cachedAttendanceLocation();
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
            rememberAttendanceLocation(payload);
            resolve(payload);
          })
          .catch(firstError => {
            requestPosition({ enableHighAccuracy: true, timeout: 35000, maximumAge: 0 })
              .then(payload => {
                rememberAttendanceLocation(payload);
                resolve(payload);
              })
              .catch(() => reject(firstError));
          });
      });
    }

    function showAttendanceLocationError(error) {
      if (Date.now() - attendanceLocationWarningShownAt < 5000) {
        return;
      }

      attendanceLocationWarningShownAt = Date.now();

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
        return;
      }

      window.dispatchEvent(new CustomEvent('toastr', {
        detail: {
          type: 'warning',
          message
        }
      }));
    }

    function requireAttendanceLocation() {
      return attendanceLocationPayload().catch((error) => {
        showAttendanceLocationError(error);
        throw error;
      });
    }

    function attendanceActionPayload() {
      if (!attendanceLocationRequired) {
        return Promise.resolve(null);
      }

      return requireAttendanceLocation();
    }
  </script>
  @endpush
</div>
