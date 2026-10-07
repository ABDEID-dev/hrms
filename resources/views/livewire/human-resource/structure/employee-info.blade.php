<div>

@php
  $configData = Helper::appClasses();
@endphp

@section('title', 'Employee Info - Structure')

@section('page-style')
  <style>
    .timeline-icon {
      cursor: pointer;
      opacity: 0;
    }

    .timeline-row:hover .timeline-icon {
      display: inline-block;
      opacity: 1;
    }
  </style>

  <style>
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

    .employee-profile-photo {
      height: 100px;
      width: 100px;
      object-fit: cover;
    }

    .employee-info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
      gap: 1rem;
    }

    .employee-info-item {
      border: 1px solid rgba(115, 103, 240, .18);
      border-radius: .5rem;
      padding: .85rem;
      background: rgba(115, 103, 240, .05);
      min-height: 76px;
    }

    .employee-info-label {
      color: #a5a3c5;
      font-size: .78rem;
      margin-bottom: .25rem;
    }

    .employee-info-value {
      color: #d7d5ee;
      font-weight: 700;
      overflow-wrap: anywhere;
    }

    @media (max-width: 575.98px) {
      .user-profile-header {
        padding-inline: 1rem;
      }

      .employee-info-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
@endsection

{{-- Alerts --}}
@include('_partials/_alerts/alert-general')

<div class="row">
  <div class="col-12">
    <div class="card mb-4">
      {{-- <div class="user-profile-header-banner">
        <img src="{{ asset('assets/img/pages/profile-banner.png') }}" alt="Banner image" class="rounded-top">
      </div> --}}
      @php
        $photoUrl = route('employee-profile-photo', ['employee' => $employee->id, 'v' => optional($employee->updated_at)->timestamp]);
      @endphp
      <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
        <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
          <img src="{{ $profilePhoto ? $profilePhoto->temporaryUrl() : $photoUrl }}" class="d-block ms-0 ms-sm-4 rounded user-profile-img employee-profile-photo">
          @if($this->canManageEmployeeProfile())
            <form wire:submit.prevent="updateProfilePhoto" class="mt-2 ms-sm-4">
              <input wire:model="profilePhoto" id="profilePhoto" type="file" class="d-none" accept="image/*">
              <label for="profilePhoto" class="btn btn-sm btn-label-primary mb-1">
                <i class="ti ti-camera me-1"></i>{{ __('Change Photo') }}
              </label>
              @if ($profilePhoto)
                <button type="submit" class="btn btn-sm btn-success mb-1" wire:loading.attr="disabled" wire:target="profilePhoto,updateProfilePhoto">
                  {{ __('Save') }}
                </button>
              @endif
              @error('profilePhoto')
                <div class="text-danger small">{{ $message }}</div>
              @enderror
            </form>
          @endif
        </div>
        <div class="flex-grow-1 mt-3 mt-sm-5">
          <div class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
            <div class="user-profile-info">
              <h4>{{ $employee->fullName }}</h4>
              <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                <li class="list-inline-item">
                  <span class="badge rounded-pill bg-label-primary"><i class="ti ti-id"></i> {{ $employee->id }}</span>
                </li>
                <li class="list-inline-item">
                  <i class="ti ti-building-community"></i> {{ $employee->current_center }}
                </li>
                <li class="list-inline-item">
                  <i class="ti ti-building"></i> {{ $employee->current_department }}
                </li>
                <li class="list-inline-item">
                  <i class="ti ti-map-pin"></i> {{ $employee->current_position }}
                </li>
                <li class="list-inline-item">
                  <i class="ti ti-rocket"></i> {{ $employee->join_at_short_form }}
                </li>
                <li class="list-inline-item">
                  <i class="ti ti-player-track-next"></i> {{ __('Continuity') . ": " . $employee->worked_years . " " . __('years') }}
                </li>
              </ul>
            </div>
            @can('manage employees')
              <button wire:click='toggleActive' type="button" class="btn @if ($employee->is_active == 1)  btn-success @else btn-danger  @endif waves-effect waves-light">
                <span class="ti @if ($employee->is_active == 1) ti-user-check @else ti-user-x @endif me-1"></span>
                @if ($employee->is_active == 1) {{ __('Active') }} @else {{ __('Inactive') }}  @endif
              </button>
            @endcan
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!--/ Header -->

<!-- Navbar pills -->
{{-- <div class="row">
  <div class="col-md-12">
    <ul class="nav nav-pills flex-column flex-sm-row mb-4">
      <li class="nav-item"><a class="nav-link active" href="javascript:void(0);"><i class='ti-xs ti ti-user-check me-1'></i> Profile</a></li>
      <li class="nav-item"><a class="nav-link" href="{{url('pages/profile-teams')}}"><i class='ti-xs ti ti-users me-1'></i> Teams</a></li>
      <li class="nav-item"><a class="nav-link" href="{{url('pages/profile-projects')}}"><i class='ti-xs ti ti-layout-grid me-1'></i> Projects</a></li>
      <li class="nav-item"><a class="nav-link" href="{{url('pages/profile-connections')}}"><i class='ti-xs ti ti-link me-1'></i> Connections</a></li>
    </ul>
  </div>
</div> --}}
<!--/ Navbar pills -->

<div class="row">
    <!-- Assets -->
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0">{{ __('Assets') }}</h5>
      </div>
      <div class="table-responsive text-nowrap">
        <table class="table table-hover">
          <thead>
            <tr>
              <th class="col-1">{{ __('ID') }}</th>
              <th class="col-1">{{ __('Category') }}</th>
              <th class="col-1">{{ __('Sub-Category') }}</th>
              <th>{{ __('Serial Number')}}</th>
              <th>{{ __('Handed Date')}}</th>
              <th>{{ __('Actions')}}</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @forelse ($employeeAssets as $asset)
              <tr>
                <td wire:click='showAsset' class="td" style="cursor: pointer;"><i class="ti ti-tag ti-sm text-danger me-3"></i> <strong>{{ $asset->asset_id }}</strong></td>
                <td>{{ $asset->getCategory($asset->asset_id)->name }}</td>
                <td>{{ $asset->getSubCategory($asset->asset_id)->name }}</td>
                <td>{{ $asset->asset->serial_number }}</td>
                <td><span class="badge rounded-pill bg-label-secondary">{{ $asset->handed_date }}</span></td>
                <td>
                  @if($this->canManageEmployeeProfile())
                    <button type="button" class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-secondary waves-effect">
                      <span class="ti ti-arrow-guide"></span>
                    </button>
                    <button type="button" class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-secondary waves-effect">
                      <span wire:click.prevent='showEditAssetModal({{ $asset }})' data-bs-toggle="modal" data-bs-target="#assetModal" class="ti ti-pencil"></span>
                    </button>
                  @else
                    <span class="text-muted">---</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6">
                  <div class="mt-2 mb-2" style="text-align: center">
                    <h3 class="mb-1 mx-2">{{ __('Oopsie-doodle!') }}</h3>
                    <p class="mb-4 mx-2">
                    {{ __('No data found, please sprinkle some data in my virtual bowl, and let the fun begin!') }}
                    </p>
                    @if($this->canManageEmployeeProfile())
                      <button class="btn btn-label-primary mb-4" data-bs-toggle="modal" data-bs-target="#assetModal">
                        {{ __('Add New Asset') }}
                      </button>
                    @endif
                    <div>
                      <img src="{{ asset('assets/img/illustrations/page-misc-under-maintenance.png') }}" width="200" class="img-fluid">
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
  <!--/ Assets -->

  <!-- Details -->
  <div class="col-12">
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-action-title mb-3">{{ __('Details') }}</h5>
        <div class="employee-info-grid mb-4">
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Full Name') }}</div>
            <div class="employee-info-value">{{ $employee->full_name ?: '---' }}</div>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Mobile') }}</div>
            <div class="employee-info-value" dir="ltr">{{ $employee->full_phone_number ? '+'.$employee->full_phone_number : '---' }}</div>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Address') }}</div>
            <div class="employee-info-value">{{ $employee->address ?: '---' }}</div>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Started') }}</div>
            <div class="employee-info-value">{{ $employee->join_at }}</div>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('National ID / Passport') }}</div>
            <div class="employee-info-value">{{ $employee->national_number ?: '---' }}</div>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Contract') }}</div>
            <div class="employee-info-value">{{ $employee->contract?->name ?: ($employee->contract_id ?: '---') }}</div>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Mother Name') }}</div>
            <div class="employee-info-value">{{ $employee->mother_name ?: '---' }}</div>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Birth & Place') }}</div>
            <div class="employee-info-value">{{ $employee->birth_and_place ?: '---' }}</div>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Degree') }}</div>
            <div class="employee-info-value">{{ $employee->degree ?: '---' }}</div>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Age') }}</div>
            <div class="employee-info-value">{{ $employee->age ?: '---' }}</div>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Gender') }}</div>
            <div class="employee-info-value">{{ $employee->gender ?: '---' }}</div>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Nationality') }}</div>
            <div class="employee-info-value">{{ $employee->nationality ?: '---' }}</div>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Job Title') }}</div>
            <div class="employee-info-value">{{ $employee->job_title ?: '---' }}</div>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Job Title (EN)') }}</div>
            <div class="employee-info-value">{{ $employee->job_title_en ?: '---' }}</div>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Job Specialization') }}</div>
            <div class="employee-info-value">{{ $employee->job_specialization ?: '---' }}</div>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Visa Type') }}</div>
            <div class="employee-info-value">{{ $employee->visa_type ?: '---' }}</div>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Leaves Balance') }}</div>
            <div class="employee-info-value">{{ $employee->balance_leave_allowed ?? '---' }} / {{ $employee->max_leave_allowed ?? '---' }}</div>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Notes') }}</div>
            <div class="employee-info-value">{{ $employee->notes ?: '---' }}</div>
          </div>
        </div>

        <h5 class="card-action-title mb-3">{{ __('Salary & Discounts') }}</h5>
        <div class="employee-info-grid mb-4">
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Basic Salary') }}</div>
            <div class="employee-info-value">AED{{ number_format($salarySummary['basic'], 2) }}</div>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Housing Allowance') }}</div>
            <div class="employee-info-value">AED{{ number_format($salarySummary['housing'], 2) }}</div>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Transportation Allowance') }}</div>
            <div class="employee-info-value">AED{{ number_format($salarySummary['transportation'], 2) }}</div>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Total Salary') }}</div>
            <div class="employee-info-value">AED{{ number_format($salarySummary['total'], 2) }}</div>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('This Month Discounts') }} - {{ $currentMonth }}</div>
            <div class="employee-info-value text-danger">AED{{ number_format($monthlyDiscountsTotal, 2) }}</div>
            <small class="text-muted">{{ $monthlyDiscountsCount }} {{ __('Records') }}</small>
          </div>
          <div class="employee-info-item">
            <div class="employee-info-label">{{ __('Net Salary') }}</div>
            <div class="employee-info-value text-success">AED{{ number_format($salarySummary['net'], 2) }}</div>
          </div>
        </div>

        <h5 class="card-action-title mb-3">{{ __('Latest Discounts') }}</h5>
        <div class="d-grid gap-2 mb-4">
          @forelse ($latestDiscounts as $discount)
            <div class="employee-info-item">
              <div class="d-flex justify-content-between gap-2">
                <span class="employee-info-value text-danger">AED{{ number_format((float) $discount->rate, 2) }}</span>
                <span class="text-muted small">{{ $discount->date }}</span>
              </div>
              <div class="employee-info-label mt-1">{{ $discount->reason ?: '---' }}</div>
            </div>
          @empty
            <div class="employee-info-item text-center text-muted">{{ __('No discounts recorded yet.') }}</div>
          @endforelse
        </div>

        <h5 class="card-action-title mb-0">{{ __('Counters') }}</h5>
        <ul class="list-unstyled mb-0 mt-3">
          <li class="d-flex align-items-center mb-3"><i class="ti ti-zzz"></i><span class="fw-bold mx-2">{{ __('Leaves Balance') }}:</span> <span class="badge bg-label-secondary">{{ $employee->max_leave_allowed . " ".__('Day') }}</span></li>
          <li class="d-flex align-items-center mb-3"><i class="ti ti-alarm"></i><span class="fw-bold mx-2">{{ __('Hourly') }}:</span> <span class="badge bg-label-secondary">{{ $employee->hourly_counter }}</span></li>
          <li class="d-flex align-items-center mb-3"><i class="ti ti-hourglass"></i><span class="fw-bold mx-2">{{ __('Delay') }}:</span> <span class="badge bg-label-secondary">{{ $employee->delay_counter }}</span></li>
        </ul>
      </div>
    </div>
  </div>
  <!--/ Details -->

  <!-- Timeline -->
  <div class="col-12">
    <div class="card card-action mb-4">
      <div class="card-header align-items-center">
        <h5 class="card-action-title mb-0">{{ __('Timelines') }}</h5>
        @if($this->canManageEmployeeProfile())
          <div class="card-action-element">
            <div class="dropdown">
              <button type="button" class="btn dropdown-toggle hide-arrow p-0" data-bs-toggle="dropdown" aria-expanded="false"><i class="ti ti-dots-vertical text-muted"></i></button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li>
                  <a wire:click='showStoreTimelineModal()' class="dropdown-item" data-bs-toggle="modal" data-bs-target="#timelineModal">{{ __('Add New Position') }}</a>
                </li>
                {{-- <li><a class="dropdown-item" href="javascript:void(0);">Edit timeline</a></li> --}}
              </ul>
            </div>
          </div>
        @endif
      </div>
      <div class="card-body pb-0">
        <ul class="timeline ms-1 mb-0">
          @foreach ($employeeTimelines as $timeline)
            <li class="timeline-item timeline-item-transparent @if ($loop->last) border-0 @endif">
              <span class="timeline-point @if ($loop->first) timeline-point-primary @else timeline-point-info @endif"></span>
              <div class="timeline-event">
                <div class="timeline-header">
                  <div class="timeline-row d-flex m-0">
                    <h6 class="m-0">{{ $timeline->position?->name ?? '---' }}</h6>
                    @if($this->canManageEmployeeProfile())
                      <i wire:click='setPresentTimeline({{ $timeline }})' class="timeline-icon text-success ti ti-refresh mx-1"></i>
                      <i wire:click='showUpdateTimelineModal({{ $timeline }})' class="timeline-icon text-info ti ti-edit" data-bs-toggle="modal" data-bs-target="#timelineModal"></i>
                      <i wire:click='confirmDeleteTimeline({{ $timeline }})' class="timeline-icon text-danger ti ti-trash mx-1"></i>
                      @if ($confirmedId === $timeline->id)
                        <button wire:click.prevent='deleteTimeline({{ $timeline }})' type="button"
                          class="btn btn-xs btn-danger waves-effect waves-light mx-1">{{ __('Sure?') }}
                        </button>
                      @endif
                    @endif
                  </div>
                  <small class="text-muted">@if ($timeline->end_date == null) {{ __('Present') }} @else {{ $timeline->start_date }} --> {{ $timeline->end_date }} @endif</small>
                </div>
                <p class="mb-2">{{ $timeline->center?->name ?? '---' }}</p>
              </div>
            </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
  <!--/ Timeline -->
</div>

{{-- Modal --}}
@include('_partials._modals.modal-timeline')

{{-- Scripts --}}
@push('custom-scripts')
  @if(session('openTimelineModal'))
    <script>
      document.addEventListener('DOMContentLoaded', function () {
          $('#timelineModal').modal('show');
      });
    </script>
  @endif
@endpush
</div>
