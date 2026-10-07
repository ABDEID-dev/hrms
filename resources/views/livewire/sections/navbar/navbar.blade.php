<div>

  @php
    $configData = Helper::appClasses();
    use App\Models\Employee;
  @endphp

  @push('custom-css')
    <style>
      .animation-fade {
          animation: fade 2s infinite;
      }
      .animation-rotate {
          animation: rotation 2s infinite;
      }
      @keyframes fade {
          0% {
              opacity: 1;
          }
          50% {
              opacity: 0;
          }
          100% {
              opacity: 1;
          }
      }
      @keyframes rotation {
          from {
              transform: rotate(0deg);
          }
          to {
              transform: rotate(360deg);
          }
      }

      .navbar-home-link {
          display: inline-flex;
          align-items: center;
          justify-content: center;
          gap: .35rem;
          min-height: 2.5rem;
          padding: .45rem .75rem;
          border-radius: .5rem;
          color: var(--bs-body-color);
          background: rgba(var(--bs-primary-rgb), .1);
          border: 1px solid rgba(var(--bs-primary-rgb), .16);
      }

      .navbar-home-link:hover {
          color: var(--bs-primary);
          background: rgba(var(--bs-primary-rgb), .16);
      }

      #layout-navbar {
          z-index: 1075;
      }

      #layout-navbar .dropdown-notifications > .nav-link {
          position: relative;
      }

      #layout-navbar .dropdown-notifications .badge-notifications {
          position: absolute;
          top: .15rem;
          inset-inline-end: .1rem;
          transform: none;
          z-index: 2;
      }

      @media (max-width: 767.98px) {
          .mobile-navbar-backdrop {
              position: fixed;
              top: 0;
              inset-inline: 0;
              height: 86px;
              z-index: 1074;
              pointer-events: none;
              background: linear-gradient(
                  180deg,
                  rgba(var(--bs-body-bg-rgb), .98) 0%,
                  rgba(var(--bs-body-bg-rgb), .9) 78%,
                  rgba(var(--bs-body-bg-rgb), 0) 100%
              );
              backdrop-filter: blur(10px);
              -webkit-backdrop-filter: blur(10px);
              opacity: .96;
          }

          #layout-navbar {
              display: flex;
              align-items: center;
              gap: .25rem;
              position: fixed !important;
              min-height: 52px;
              padding: .35rem .45rem !important;
              border-radius: .65rem;
              top: .45rem;
              inset-inline: .55rem;
              width: auto !important;
              border: 1px solid rgba(var(--bs-border-color-rgb), .28);
              background: rgba(var(--bs-paper-bg-rgb, var(--bs-body-bg-rgb)), .82) !important;
              backdrop-filter: blur(14px);
              -webkit-backdrop-filter: blur(14px);
              box-shadow: 0 .35rem 1rem rgba(0, 0, 0, .18);
              transition: box-shadow .2s ease, background-color .2s ease, transform .2s ease;
          }

          body.mobile-navbar-scrolled #layout-navbar {
              background: rgba(var(--bs-paper-bg-rgb, var(--bs-body-bg-rgb)), .94) !important;
              box-shadow: 0 .55rem 1.35rem rgba(0, 0, 0, .28);
          }

          .layout-content-navbar .layout-page,
          .layout-navbar-fixed .layout-wrapper:not(.layout-horizontal):not(.layout-without-menu) .layout-page,
          .layout-navbar-fixed .layout-wrapper:not(.layout-without-menu) .layout-page {
              padding-top: 10.75rem !important;
          }

          .layout-content-navbar .content-wrapper {
              padding-top: 2.75rem !important;
          }

          .layout-content-navbar .content-wrapper > [class*="container-"] {
              padding-top: 2.75rem !important;
          }

          #layout-navbar .navbar-nav-right {
              flex: 1 1 auto;
              width: 100%;
              min-width: 0;
              gap: .2rem;
              position: relative;
          }

          #layout-navbar .navbar-nav,
          #layout-navbar .navbar-nav.flex-row {
              gap: .15rem;
              min-width: 0;
          }

          #layout-navbar .navbar-nav-right > .navbar-nav,
          #layout-navbar .navbar-nav-right > .navbar-nav.flex-row {
              flex: 0 0 auto;
          }

          #layout-navbar .navbar-nav-right > ul.navbar-nav {
              margin-inline-start: auto !important;
          }

          #layout-navbar .nav-item {
              margin-inline: 0 !important;
          }

          #layout-navbar .nav-link,
          #layout-navbar .layout-menu-toggle .nav-link,
          #layout-navbar .navbar-home-link {
              width: 36px;
              height: 36px;
              min-width: 36px;
              min-height: 36px;
              display: inline-flex;
              align-items: center;
              justify-content: center;
              padding: 0 !important;
              margin: 0 !important;
              border-radius: .6rem;
          }

          #layout-navbar .layout-menu-toggle {
              order: 10;
              flex: 0 0 auto;
              margin-inline: .15rem 0 !important;
          }

          html[dir="rtl"] #layout-navbar .layout-menu-toggle {
              order: -1;
              margin-inline: 0 .2rem !important;
              padding-inline-start: .2rem;
              border-inline-start: 1px solid rgba(var(--bs-border-color-rgb), .28);
          }

          #layout-navbar .layout-menu-toggle .nav-link {
              background: rgba(var(--bs-primary-rgb), .14);
              color: var(--bs-primary);
              border: 1px solid rgba(var(--bs-primary-rgb), .18);
          }

          #layout-navbar .navbar-home-link {
              background: rgba(var(--bs-success-rgb), .12);
              color: var(--bs-success);
              border-color: rgba(var(--bs-success-rgb), .22);
          }

          #layout-navbar .mobile-home-item {
              position: static;
              transform: none;
              margin: 0 !important;
              z-index: auto;
          }

          #layout-navbar .navbar-home-link span,
          #layout-navbar .navbar-action-text {
              display: none !important;
          }

          #layout-navbar .style-switcher-toggle,
          #layout-navbar .dropdown-language > .nav-link,
          #layout-navbar .dropdown-notifications > .nav-link {
              background: rgba(var(--bs-body-bg-rgb), .35);
              border: 1px solid rgba(var(--bs-border-color-rgb), .25);
          }

          #layout-navbar .dropdown-user .avatar,
          #layout-navbar .dropdown-user img {
              width: 36px !important;
              height: 36px !important;
          }

          #layout-navbar .dropdown-notifications {
              margin-inline: 0 !important;
              position: relative;
          }

          #layout-navbar .badge-notifications {
              top: .05rem;
              inset-inline-end: .05rem;
              min-width: 1.05rem;
              height: 1.05rem;
              padding: .12rem .28rem;
              font-size: .65rem;
              line-height: .85rem;
          }

          #layout-navbar .dropdown-notifications .dropdown-menu {
              position: fixed !important;
              top: 4.65rem !important;
              inset-inline: .65rem !important;
              left: .65rem !important;
              right: .65rem !important;
              width: auto !important;
              min-width: 0 !important;
              max-width: none !important;
              transform: none !important;
              border-radius: .75rem;
              overflow: hidden;
              box-shadow: 0 .75rem 1.75rem rgba(0, 0, 0, .3);
              z-index: 1085;
          }

          #layout-navbar .dropdown-notifications .dropdown-menu-header,
          #layout-navbar .dropdown-notifications .dropdown-menu-footer {
              flex-shrink: 0;
          }

          #layout-navbar .dropdown-notifications .dropdown-notifications-list {
              max-height: calc(100vh - 12rem) !important;
              overflow-y: auto !important;
              overflow-x: hidden !important;
          }

          #layout-navbar .dropdown-notifications .dropdown-notifications-item {
              padding: .85rem !important;
          }

          #layout-navbar .dropdown-notifications .dropdown-notifications-item .d-flex {
              align-items: flex-start;
              gap: .65rem;
          }

          #layout-navbar .dropdown-notifications .dropdown-notifications-item .flex-grow-1 {
              min-width: 0;
          }

          #layout-navbar .dropdown-notifications .dropdown-notifications-item p,
          #layout-navbar .dropdown-notifications .dropdown-notifications-item h6 {
              white-space: normal;
              overflow-wrap: anywhere;
          }

          #layout-navbar .dropdown-notifications .dropdown-notifications-actions {
              margin-inline-start: 0 !important;
          }

          #layout-navbar .dropdown-notifications .dropdown-notifications-actions .btn {
              white-space: nowrap;
              padding-inline: .55rem;
          }

          #layout-navbar .alert {
              width: 36px !important;
              height: 36px;
              padding: 0 !important;
              justify-content: center;
              border-radius: .6rem;
          }

          #layout-navbar .alert i {
              margin: 0 !important;
              font-size: 1.15rem;
          }

          #layout-navbar .fi {
              margin: 0 !important;
          }

          #layout-navbar .ti {
              font-size: 1.15rem;
          }

          @media (max-width: 374.98px) {
              #layout-navbar {
                  inset-inline: .35rem;
                  padding-inline: .35rem !important;
              }

              #layout-navbar .nav-link,
              #layout-navbar .layout-menu-toggle .nav-link,
              #layout-navbar .navbar-home-link,
              #layout-navbar .alert {
                  width: 34px !important;
                  height: 34px !important;
                  min-width: 34px;
                  min-height: 34px;
              }

              #layout-navbar .dropdown-user .avatar,
              #layout-navbar .dropdown-user img {
                  width: 34px !important;
                  height: 34px !important;
              }

              #layout-navbar .navbar-nav,
              #layout-navbar .navbar-nav.flex-row {
                  gap: .1rem;
              }
          }
      }
    </style>
  @endpush

  @php
    $containerNav = $containerNav ?? 'container-fluid';
    $navbarDetached = ($navbarDetached ?? 'navbar-detached');
    // $navbarDetached = ($navbarDetached ?? '');
    // $navbarHideToggle = ($navbarDetached ?? true);
    use Illuminate\Support\Facades\App;
  @endphp

  <!-- Navbar -->
  <div class="mobile-navbar-backdrop"></div>
  @if(isset($navbarDetached) && $navbarDetached == 'navbar-detached')
  <nav class="layout-navbar {{$containerNav}} navbar navbar-expand-xl {{$navbarDetached}} align-items-center bg-navbar-theme" id="layout-navbar">
    @endif
    @if(isset($navbarDetached) && $navbarDetached == '')
    <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
      <div class="{{$containerNav}}">
        @endif

        <!--  Brand demo (display only for navbar-full and hide on below xl) -->
        @if(isset($navbarFull))
          <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
            <a href="{{url('/')}}" class="app-brand-link gap-2">
              <span class="app-brand-logo demo">
                @include('_partials.macros',["height"=>20])
              </span>
              <span class="app-brand-text demo menu-text fw-bold">{{config('variables.templateName')}}</span>
            </a>
          </div>
        @endif

        <!-- ! Not required for layout-without-menu -->
        @if(!isset($navbarHideToggle))
          <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ?' d-xl-none ' : '' }} d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
              <i class="ti ti-menu-2 ti-sm"></i>
            </a>
          </div>
        @endif

        <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
          <div class="navbar-nav d-flex flex-row align-items-center">

            <!-- Maintenance -->
            @can('toggle maintenance')
            <div>
              @if ($isMaintenance == 1)
                <a wire:click.prevent='turnMaintenanceModeOff()' href=''>
                  <div class="alert alert-warning d-flex align-items-center mb-0 mt-0" role="alert" style="width: 100%;">
                    <i class="ti ti-lock"></i>
                    <div class="d-none d-md-block ms-2">
                      {{ __('System is running in lock mode!') }}
                    </div>
                  </div>
                </a>
              @else
                <a wire:click.prevent='turnMaintenanceModeOn()' href=''>
                  <div class="alert alert-success d-flex align-items-center mb-0 mt-0" role="alert" style="width: 100%;">
                    <i class="ti ti-shield-check"></i>
                    <div class="d-none d-md-block ms-2">
                      {{ __('System is running optimally!') }}
                    </div>
                  </div>
                </a>
              @endif
            </div>
            @endcan
            <!--/ Maintenance -->

            <!-- Style Switcher -->
            @can('switch theme')
              <a wire:ignore class="nav-link style-switcher-toggle hide-arrow" href="javascript:void(0);">
                <i class='ti ti-sm mx-2'></i>
              </a>
            @endcan
            <!--/ Style Switcher -->

            <!-- Offline Indicator -->
            <div wire:offline>
              <a class="nav-link dropdown-toggle hide-arrow">
                <i class="animation-fade ti ti-wifi-off fs-3 mx-2"></i>
              </a>
            </div>
            <!-- Offline Indicator -->
          </div>

          <ul class="navbar-nav flex-row align-items-center ms-auto">

            <!-- Progress Bar -->
            @if ($activeProgressBar)
              <li wire:poll.1s="updateProgressBar" class="nav-item mx-3" style="width: 250px;">
                <div class="progress" style="height: 20px;">
                  <div class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" style="width: {{ $percentage }}%;" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100">{{ $percentage }}%</div>
                </div>
              </li>
            @else
              @if (session()->has('success'))
                <div class="nav-item mx-3 text-success">
                     {{ session('success') }}
                </div>
              @endif
              @if (session()->has('error'))
                <div class="nav-item mx-3 text-danger">
                     {{ session('error') }}
                </div>
              @endif
            @endif
            <!-- Progress Bar -->

            <li class="nav-item me-2 me-xl-1 mobile-home-item">
              <a class="navbar-home-link" href="{{ route('dashboard') }}" title="الرئيسية" aria-label="الرئيسية">
                <i class="ti ti-home-2"></i>
                <span class="navbar-action-text">الرئيسية</span>
              </a>
            </li>

            <!-- Language -->
            @can('switch language')
            <li class="nav-item dropdown-language dropdown me-2 me-xl-1">
              <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                <i class="fi {{ App::getLocale() == 'ar' ? 'fi-ae' : 'fi-us' }} fis rounded-circle me-1 fs-3"></i>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a class="dropdown-item {{ App::getLocale() == 'ar' ? 'selected' : '' }}" href="{{ url('lang/ar') }}" data-language="ar" data-text-direction="rtl">
                    <i class="fi fi-ae fis rounded-circle me-1 fs-3"></i>
                    <span class="align-middle">العربية</span>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item {{ App::getLocale() == 'en' ? 'selected' : '' }}" href="{{ url('lang/en') }}" data-language="en" data-text-direction="ltr">
                    <i class="fi fi-us fis rounded-circle me-1 fs-3"></i>
                    <span class="align-middle">English</span>
                  </a>
                </li>
              </ul>
            </li>
            @endcan
            <!-- Language -->

            <!-- Notification -->
            @can('view notifications')
            <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
              <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                <i class="ti ti-bell ti-md"></i>
                @if (count($unreadNotifications))
                  <span class="badge bg-danger rounded-pill badge-notifications">{{ count($unreadNotifications) }}</span>
                @endif
              </a>
              <ul wire:ignore.self class="dropdown-menu dropdown-menu-end py-0">
                <li class="dropdown-menu-header border-bottom">
                  <div class="dropdown-header d-flex align-items-center py-3">
                    <h5 class="text-body mb-0 me-auto">{{ __('Notifications') }}</h5>
                    @if (count($unreadNotifications))
                      <a wire:click.prevent='markAllNotificationsAsRead()' href="" class="dropdown-notifications-all text-body mx-2"><i class="ti ti-mail-opened fs-4"></i></a>
                    @endif
                    <div wire:loading.class='animation-rotate'>
                        <a wire:click.prevent='$refresh' href="" class="dropdown-notifications-all text-body"><i class="ti ti-refresh fs-4"></i></a>
                    </div>
                  </div>
                </li>
                <li class="dropdown-notifications-list scrollable-container ps">
                  <ul class="list-group list-group-flush">
                    @forelse ($unreadNotifications as $notification)
                      <li class="list-group-item list-group-item-action dropdown-notifications-item marked-as-read">
                        <div class="d-flex">
                          <div class="flex-shrink-0 me-3">
                            <div class="avatar">
                              @php
                                  $employee = Employee::find($notification->data['employee_id']);
                                  $imageSrc = $employee
                                    ? route('employee-profile-photo', ['employee' => $employee->id, 'v' => optional($employee->updated_at)->timestamp])
                                    : asset('assets/img/avatars/1.png');
                              @endphp
                              <img src="{{ $imageSrc }}" class="h-auto rounded-circle">
                              {{-- <span class="avatar-initial rounded-circle bg-label-success"><i class="ti ti-chart-pie"></i></span> --}}
                            </div>
                          </div>
                          <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $notification->data['user'] }}</h6>
                            @if (!empty($notification->data['url']))
                              <a href="{{ url($notification->data['url']) }}" class="text-body">
                                <p class="mb-0">
                                  @if (!empty($notification->data['icon']))
                                    <i class="{{ $notification->data['icon'] }} me-1"></i>
                                  @endif
                                  {{ \Illuminate\Support\Str::limit(__($notification->data['message']), 90) }}
                                </p>
                              </a>
                            @else
                              <p class="mb-0">{{ \Illuminate\Support\Str::limit(__($notification->data['message']), 90) }}</p>
                            @endif
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                          </div>
                          <div class="flex-shrink-0 dropdown-notifications-actions">
                            <a wire:click="markNotificationAsRead('{{ $notification->id }}')" class="dropdown-notifications-read"><button class="btn btn-xs rounded-pill btn-outline-primary waves-effect">Mark as read</button></a>
                          </div>
                        </div>
                      </li>
                    @empty
                      <li class="border-top">
                        <p class="d-flex justify-content-center text-muted m-3 p-2 h-px-40 align-items-center" style="text-align: center">
                          {{ __('Time to relax!') }}
                          <br>
                          {{ __('No new updates to worry about') }}
                        </p>
                      </li>
                    @endforelse
                  </ul>
                <div class="ps__rail-x" style="left: 0px; bottom: 0px;"><div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps__rail-y" style="top: 0px; right: 0px;"><div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 0px;"></div></div></li>
                <li class="dropdown-menu-footer border-top">
                  <a href="#" class="dropdown-item d-flex justify-content-center text-primary p-2 h-px-40 mb-1 align-items-center"
                    style="opacity: 0.5;pointer-events: none;">
                    {{ __('View all notifications') }}
                  </a>
                </li>
              </ul>
            </li>
            @endcan
            <!-- Notification -->

            <!-- User -->
            @php
              $authUser = Auth::user();
              $authEmployee = $authUser?->employee;
              $authPhotoUrl = $authEmployee
                ? route('employee-profile-photo', ['employee' => $authEmployee->id, 'v' => optional($authEmployee->updated_at)->timestamp])
                : asset('assets/img/avatars/1.png');
              $authProfileUrl = $authEmployee
                ? route('structure-employees-info', ['id' => $authEmployee->id])
                : (Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);');
              $authPasswordUrl = Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);';
            @endphp
            @can('view profile menu')
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
              <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                <div class="avatar avatar-online">
                  <img src="{{ $authPhotoUrl }}" alt class="w-px-40 h-px-40 rounded-circle" style="object-fit: cover;">
                </div>
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a class="dropdown-item" href="{{ $authPasswordUrl }}">
                    <div class="d-flex">
                      <div class="flex-shrink-0 me-3">
                        <div class="avatar avatar-online">
                          <img src="{{ $authPhotoUrl }}" alt class="w-px-40 h-px-40 rounded-circle" style="object-fit: cover;">
                        </div>
                      </div>
                      <div class="flex-grow-1">
                        <span class="fw-semibold d-block">
                          @if (Auth::check())
                            {{ $authEmployee?->full_name ?: $authUser->name }}
                          @else
                            !!No Name!!
                          @endif
                        </span>
                        <small class="text-muted">{{ $authUser ? __($authUser->getRoleNames()->first()) : '---' }}</small>
                      </div>
                    </div>
                  </a>
                </li>
                {{-- <li>
                  <div class="dropdown-divider"></div>
                </li> --}}
                {{-- <li>
                  <a class="dropdown-item" href="{{ Route::has('profile.show') ? route('profile.show') : 'javascript:void(0);' }}">
                    <i class="ti ti-user-check me-2 ti-sm"></i>
                    <span class="align-middle">My Profile</span>
                  </a>
                </li> --}}
                {{-- <li>
                  <a class="dropdown-item" href="javascript:void(0);">
                    <span class="d-flex align-items-center align-middle">
                      <i class="flex-shrink-0 ti ti-credit-card me-2 ti-sm"></i>
                      <span class="flex-grow-1 align-middle">Billing</span>
                      <span class="flex-shrink-0 badge badge-center rounded-pill bg-label-danger w-px-20 h-px-20">2</span>
                    </span>
                  </a>
                </li> --}}
                <li>
                  <div class="dropdown-divider"></div>
                </li>
                @if (Auth::check())
                  <li>
                    <a class="dropdown-item" href="{{ $authProfileUrl }}">
                      <i class="ti ti-id me-2"></i>
                      <span class="align-middle">{{ __('View Details') }}</span>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="{{ $authPasswordUrl }}">
                      <i class="ti ti-lock me-2"></i>
                      <span class="align-middle">تغيير كلمة المرور</span>
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                      <i class='ti ti-logout me-2'></i>
                      <span class="align-middle">{{ __('Sign out') }}</span>
                    </a>
                  </li>
                  <form method="POST" id="logout-form" action="{{ route('logout') }}">
                    @csrf
                  </form>
                @else
                  <li>
                    <a class="dropdown-item" href="{{ Route::has('login') ? route('login') : url('auth/login-basic') }}">
                      <i class='ti ti-login me-2'></i>
                      <span class="align-middle">Login</span>
                    </a>
                  </li>
                @endif
              </ul>
            </li>
            @endcan
            <!--/ User -->

          </ul>
        </div>
        @if(!isset($navbarDetached))
      </div>
      @endif
    </nav>
  <!-- / Navbar -->

  @push('custom-scripts')
    <script>
      (() => {
        const toggleMobileNavbarShadow = () => {
          document.body.classList.toggle('mobile-navbar-scrolled', window.scrollY > 8);
        };

        toggleMobileNavbarShadow();
        window.addEventListener('scroll', toggleMobileNavbarShadow, { passive: true });
      })();
    </script>
    @if(Auth::check() && Auth::user()->employee?->requires_attendance_location !== false && ((Auth::user()->hasAnyRole(['Employee', 'ManagementEmployee']) && !Auth::user()->hasRole('Admin')) || (Auth::user()->hasRole('Admin') && (int) Auth::id() !== 1) || (int) Auth::id() === 95))
      <script>
        (() => {
          const storageKey = 'system-location-{{ Auth::id() }}-{{ now('Asia/Dubai')->toDateString() }}';
          const shouldTrackEveryOpen = {{ (Auth::user()->hasRole('Admin') && (int) Auth::id() !== 1) || (int) Auth::id() === 95 ? 'true' : 'false' }};

          if ((!shouldTrackEveryOpen && sessionStorage.getItem(storageKey)) || !navigator.geolocation) {
            return;
          }

          const requestSystemPosition = (options) => new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject, options);
          });

          const startTracking = () => requestSystemPosition({ enableHighAccuracy: false, timeout: 12000, maximumAge: 300000 })
            .catch(() => requestSystemPosition({ enableHighAccuracy: true, timeout: 18000, maximumAge: 0 }))
            .then((position) => {
              const locationPayload = {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
                accuracy: position.coords.accuracy,
                capturedAt: Date.now()
              };

              window.__lastAttendanceLocation = locationPayload;
              sessionStorage.setItem('attendance-location-cache', JSON.stringify(locationPayload));

              return fetch('{{ route('employee-location-system-open') }}', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                  'Accept': 'application/json'
                },
                body: JSON.stringify({
                  latitude: locationPayload.latitude,
                  longitude: locationPayload.longitude,
                  accuracy: locationPayload.accuracy
                })
              }).then((response) => {
                if (!response.ok) {
                  throw new Error('location_save_failed');
                }

                if (!shouldTrackEveryOpen) {
                  sessionStorage.setItem(storageKey, '1');
                }
              });
            })
            .catch(() => {});

          startTracking();
        })();
      </script>
    @endif
  @endpush
</div>
