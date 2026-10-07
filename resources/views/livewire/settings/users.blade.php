<div>
  @section('title', __('Users'))

  @section('page-style')
    <style>
      .users-page .user-card {
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .5rem;
        padding: 1rem;
        height: 100%;
        background: rgba(var(--bs-body-bg-rgb), .35);
      }

      .users-page .user-meta {
        color: var(--bs-secondary-color);
        font-size: .85rem;
      }

      @media (max-width: 767.98px) {
        .users-page {
          margin-inline: -.75rem;
        }

        .users-page .card {
          border-radius: 0;
        }
      }
    </style>
  @endsection

  @include('_partials/_alerts/alert-general')

  <div class="users-page">
    <div class="card mb-4">
      <div class="card-header border-bottom">
        <h5 class="mb-0">{{ __('Create User Account') }}</h5>
        <small class="text-muted">اختر موظف موجود لإنشاء حساب دخول مرتبط به. لا يتم إنشاء موظفين فارغين من هنا.</small>
      </div>
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-lg-3 col-md-6">
            <label class="form-label">الموظف</label>
            <select wire:model.live="userForm.employee_id" class="form-select @error('userForm.employee_id') is-invalid @enderror">
              <option value="">اختر موظف بدون حساب</option>
              @foreach($availableEmployees as $employee)
                <option value="{{ $employee->id }}">#{{ $employee->id }} - {{ $employee->full_name }}</option>
              @endforeach
            </select>
            @error('userForm.employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-3 col-md-6">
            <label class="form-label">{{ __('Account Name') }}</label>
            <input wire:model.defer="userForm.name" type="text" class="form-control @error('userForm.name') is-invalid @enderror">
            @error('userForm.name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-2 col-md-6">
            <label class="form-label">{{ __('Username') }}</label>
            <input wire:model.defer="userForm.username" type="text" class="form-control @error('userForm.username') is-invalid @enderror">
            @error('userForm.username')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-2 col-md-6">
            <label class="form-label">{{ __('Password') }}</label>
            <input wire:model.defer="userForm.password" type="text" class="form-control @error('userForm.password') is-invalid @enderror">
            @error('userForm.password')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-2 col-md-6">
            <label class="form-label">{{ __('Email') }}</label>
            <input wire:model.defer="userForm.email" type="email" class="form-control @error('userForm.email') is-invalid @enderror">
            @error('userForm.email')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          @if($this->canManageEmployeeRoles())
            <div class="col-lg-2 col-md-6">
              <label class="form-label">{{ __('Role') }}</label>
              <select wire:model.live="userForm.role" class="form-select @error('userForm.role') is-invalid @enderror">
                @foreach($availableRoles as $role)
                  <option value="{{ $role->name }}">{{ $role->display_name ?: __($role->name) }}</option>
                @endforeach
              </select>
              @error('userForm.role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
          @endif
          <div class="col-lg-1 col-md-6">
            <button wire:click="createUser" type="button" class="btn btn-primary w-100">
              <i class="ti ti-plus"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
          <h5 class="mb-0">{{ __('System Accounts') }}</h5>
          <small class="text-muted">{{ __('All login accounts linked to employee profiles.') }}</small>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <select wire:model.live="roleFilter" class="form-select" style="max-width: 240px;">
            <option value="">كل الأدوار</option>
            @foreach($availableRoles as $role)
              <option value="{{ $role->name }}">{{ $role->display_name ?: __($role->name) }}</option>
            @endforeach
          </select>
          <input wire:model.live.debounce.300ms="searchTerm" type="text" class="form-control" style="max-width: 260px;" placeholder="{{ __('Search...') }}">
        </div>
      </div>
      <div class="card-body">
        <div class="row g-3">
          @forelse($users as $user)
            <div class="col-xl-4 col-md-6">
              <div class="user-card">
                <div class="d-flex justify-content-between gap-3">
                  <div>
                    <h6 class="mb-1">{{ $user->name }}</h6>
                    <div class="user-meta">{{ __('Username') }}: {{ $user->username ?: '---' }}</div>
                    <div class="user-meta">{{ __('Employee') }}: {{ $user->employee?->full_name ?: '---' }}</div>
                    <div class="user-meta">{{ __('Employee ID') }}: {{ $user->employee_id ?: '---' }}</div>
                    <div class="user-meta">{{ __('Role') }}: {{ $user->roles->pluck('name')->implode(', ') ?: '---' }}</div>
                    <div class="user-meta">{{ __('Accounts Branch') }}: {{ $user->account_access_labels }}</div>
                    <div class="user-meta mt-2">الفروع والصلاحيات يتم تحديدها من الدور المرتبط بالحساب.</div>
                  </div>
                  <div class="text-end">
                    @if($user->employee_id)
                      <a href="{{ route('structure-employees-info', $user->employee_id) }}" class="btn btn-sm btn-icon btn-label-info" title="{{ __('Employee profile') }}">
                        <i class="ti ti-user"></i>
                      </a>
                    @endif
                    @if($this->canManageEmployeeRoles() && (! $user->hasRole('Admin') || $this->canAssignPrimaryAdminRole()))
                      <button wire:click="showEditUserRole({{ $user->id }})" type="button" class="btn btn-sm btn-icon btn-label-primary" title="تغيير الدور">
                        <i class="ti ti-user-cog"></i>
                      </button>
                    @endif
                    @if($user->id !== 1)
                      <button wire:click="confirmDeleteUser({{ $user->id }})" type="button" class="btn btn-sm btn-icon btn-label-danger">
                        <i class="ti ti-trash"></i>
                      </button>
                    @endif
                  </div>
                </div>
                @if($editingUserRoleId === $user->id)
                  <div class="mt-3 p-3 rounded border">
                    <label class="form-label">تغيير دور المستخدم</label>
                    <div class="d-flex flex-wrap gap-2">
                      <select wire:model.defer="roleEditForm.role" class="form-select" style="max-width: 260px;">
                        @foreach($availableRoles as $role)
                          <option value="{{ $role->name }}">{{ $role->display_name ?: __($role->name) }}</option>
                        @endforeach
                      </select>
                      <button wire:click="updateUserRole" type="button" class="btn btn-primary">
                        {{ __('Save') }}
                      </button>
                      <button wire:click="cancelEditUserRole" type="button" class="btn btn-label-secondary">
                        {{ __('Cancel') }}
                      </button>
                    </div>
                    @error('roleEditForm.role')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                  </div>
                @endif
                @if($confirmedUserId === $user->id)
                  <div class="mt-3">
                    <button wire:click="deleteUser" type="button" class="btn btn-sm btn-danger">{{ __('Sure?') }}</button>
                  </div>
                @endif
              </div>
            </div>
          @empty
            <div class="col-12 text-center text-muted py-5">{{ __('No data found') }}</div>
          @endforelse
        </div>
      </div>
      <div class="card-footer">
        {{ $users->links() }}
      </div>
    </div>
  </div>
</div>
