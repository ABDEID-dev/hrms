<div>
  @section('title', __('Roles'))

  @section('page-style')
    <style>
      .roles-page .role-card {
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .5rem;
        background: rgba(var(--bs-body-bg-rgb), .35);
        height: 100%;
        overflow: hidden;
      }

      .roles-page .role-card-header {
        padding: .9rem 1rem;
        border-bottom: 1px solid rgba(var(--bs-border-color-rgb), .35);
      }

      .roles-page .role-card-body {
        padding: .85rem 1rem 1rem;
      }

      .roles-page .role-meta-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .55rem;
        margin-top: .75rem;
      }

      .roles-page .role-meta-item {
        border: 1px solid rgba(var(--bs-border-color-rgb), .3);
        border-radius: .45rem;
        padding: .55rem .65rem;
        background: rgba(var(--bs-body-bg-rgb), .22);
      }

      .roles-page .role-meta-item small {
        display: block;
        color: var(--bs-secondary-color);
        line-height: 1.2;
      }

      .roles-page .role-meta-item strong {
        display: block;
        margin-top: .15rem;
        font-size: .95rem;
      }

      .roles-page .permission-chip {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        padding: .35rem .6rem;
        border-radius: 999px;
        background: rgba(115, 103, 240, .12);
        color: #a99dff;
        font-size: .78rem;
        margin: .2rem;
      }

      .roles-page .role-permissions-more {
        margin-top: .85rem;
      }

      .roles-page .role-permissions-more summary {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        cursor: pointer;
        user-select: none;
        color: var(--bs-primary);
        font-size: .85rem;
        font-weight: 600;
        padding: .55rem .75rem;
        border-radius: .45rem;
        background: rgba(115, 103, 240, .1);
        border: 1px solid rgba(115, 103, 240, .22);
      }

      .roles-page .role-permissions-more summary::-webkit-details-marker {
        display: none;
      }

      .roles-page .role-permissions-more summary .toggle-icon {
        transition: transform .18s ease;
      }

      .roles-page .role-permissions-more[open] summary .toggle-icon {
        transform: rotate(180deg);
      }

      .roles-page .role-permissions-extra {
        margin-top: .75rem;
        max-height: 220px;
        overflow: auto;
        padding: .5rem;
        border: 1px solid rgba(var(--bs-border-color-rgb), .28);
        border-radius: .5rem;
        background: rgba(var(--bs-body-bg-rgb), .22);
      }

      .roles-page .permission-checks {
        max-height: 360px;
        overflow: auto;
        border: 1px solid rgba(var(--bs-border-color-rgb), .35);
        border-radius: .5rem;
        padding: 1rem;
      }

      .roles-page .roles-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
      }

      .roles-page .role-form-modal .modal-dialog {
        max-width: min(1180px, calc(100vw - 2rem));
      }

      .roles-page .role-form-modal .modal-body {
        max-height: calc(100vh - 13rem);
        overflow: auto;
      }

      .roles-page .project-preset {
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .5rem;
        padding: 1rem;
        height: 100%;
        background: rgba(var(--bs-body-bg-rgb), .25);
      }

      .roles-page .project-preset.is-active {
        border-color: rgba(115, 103, 240, .7);
        background: rgba(115, 103, 240, .12);
      }

      .roles-page .permission-group-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .75rem;
        border-radius: .45rem;
        background: rgba(var(--bs-body-bg-rgb), .35);
      }

      .roles-page .permission-group-title h6 {
        margin: 0;
      }

      @media (max-width: 767.98px) {
        .roles-page {
          margin-inline: -.75rem;
        }

        .roles-page .card {
          border-radius: 0;
        }
      }
    </style>
  @endsection

  @include('_partials/_alerts/alert-general')

  <div class="roles-page">
    <div class="card mb-4 d-none">
      <div class="card-header border-bottom">
        <h5 class="mb-0">{{ $editingRoleId ? __('Edit Role') : __('Create Role') }}</h5>
        <small class="text-muted">{{ __('Create roles such as HR, accountant, management employee, then attach system permissions to each role.') }}</small>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-lg-3 col-md-6">
            <label class="form-label">{{ __('System Role Name') }}</label>
            <input wire:model.defer="roleForm.name" type="text" dir="ltr" class="form-control @error('roleForm.name') is-invalid @enderror" placeholder="Accountant">
            @error('roleForm.name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-3 col-md-6">
            <label class="form-label">{{ __('Display name') }}</label>
            <input wire:model.defer="roleForm.display_name" type="text" class="form-control @error('roleForm.display_name') is-invalid @enderror" placeholder="مسؤول حسابات">
            @error('roleForm.display_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-6 col-12">
            <label class="form-label">{{ __('Description') }}</label>
            <input wire:model.defer="roleForm.description" type="text" class="form-control @error('roleForm.description') is-invalid @enderror" placeholder="{{ __('Short role description') }}">
            @error('roleForm.description')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <div class="col-12">
            <label class="form-label">{{ __('Permissions') }}</label>
            <div class="row g-3 mb-3">
              @foreach($projectPermissionPresets as $presetKey => $preset)
                @php
                  $presetPermissions = $preset['permissions'] ?? [];
                  $selectedPresetPermissions = array_intersect($presetPermissions, $roleForm['permissions'] ?? []);
                  $isPresetActive = count($presetPermissions) > 0 && count($selectedPresetPermissions) === count($presetPermissions);
                @endphp
                <div class="col-xl-4 col-md-6">
                  <div class="project-preset {{ $isPresetActive ? 'is-active' : '' }}">
                    <div class="d-flex justify-content-between gap-3 mb-2">
                      <div>
                        <h6 class="mb-1">{{ $preset['label'] }}</h6>
                        <div class="text-muted small">{{ $preset['description'] }}</div>
                      </div>
                      <span class="badge bg-label-primary align-self-start">{{ count($selectedPresetPermissions) }}/{{ count($presetPermissions) }}</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                      <button wire:click="applyProjectPreset('{{ $presetKey }}')" type="button" class="btn btn-sm btn-primary">
                        <i class="ti ti-check me-1"></i>إضافة المشروع
                      </button>
                      <button wire:click="removeProjectPreset('{{ $presetKey }}')" type="button" class="btn btn-sm btn-label-secondary">
                        إزالة
                      </button>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>

            <div class="permission-checks">
              @foreach($permissions->groupBy(fn($permission) => $permission->group_name ?: __('General')) as $groupName => $groupPermissions)
                <div class="mb-3">
                  <div class="permission-group-title mb-2">
                    <div>
                      <h6>{{ $groupName }}</h6>
                      <div class="text-muted small">{{ $groupPermissions->count() }} صلاحية</div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                      <button wire:click="selectPermissionGroup(@js($groupName))" type="button" class="btn btn-xs btn-label-primary">
                        تحديد القسم
                      </button>
                      <button wire:click="clearPermissionGroup(@js($groupName))" type="button" class="btn btn-xs btn-label-secondary">
                        مسح القسم
                      </button>
                    </div>
                  </div>
                  <div class="row g-2">
                    @foreach($groupPermissions as $permission)
                      <div class="col-xl-3 col-md-4 col-sm-6 col-12">
                        <div class="form-check">
                          <input
                            wire:model.defer="roleForm.permissions"
                            class="form-check-input"
                            type="checkbox"
                            value="{{ $permission->name }}"
                            id="rolePermission{{ $permission->id }}"
                          >
                          <label class="form-check-label" for="rolePermission{{ $permission->id }}">
                            {{ $permission->display_name ?: $permission->name }}
                          </label>
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
              @endforeach
            </div>
            @error('roleForm.permissions')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
          </div>

          <div class="col-12 d-flex flex-wrap gap-2">
            <button wire:click="saveRole" type="button" class="btn btn-primary">
              <i class="ti ti-device-floppy me-1"></i>{{ $editingRoleId ? __('Save') : __('Create Role') }}
            </button>
            @if($editingRoleId)
              <button wire:click="cancelEdit" type="button" class="btn btn-label-secondary">
                {{ __('Cancel') }}
              </button>
            @endif
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-body roles-toolbar">
        <div>
          <h5 class="mb-1">{{ __('Roles') }}</h5>
          <small class="text-muted">{{ __('Create roles and attach system permissions to each role.') }}</small>
        </div>
        <button wire:click="showCreateRoleModal" type="button" class="btn btn-primary">
          <i class="ti ti-plus me-1"></i>{{ __('Create Role') }}
        </button>
      </div>
    </div>

    <div wire:ignore.self class="modal fade role-form-modal" id="roleFormModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <div>
              <h5 class="modal-title">{{ $editingRoleId ? __('Edit Role') : __('Create Role') }}</h5>
              <small class="text-muted">{{ __('Create roles and attach system permissions to each role.') }}</small>
            </div>
            <button wire:click="cancelEdit" type="button" class="btn-close" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-lg-3 col-md-6">
                <label class="form-label">{{ __('System Role Name') }}</label>
                <input wire:model.defer="roleForm.name" type="text" dir="ltr" class="form-control @error('roleForm.name') is-invalid @enderror" placeholder="Accountant">
                @error('roleForm.name')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-3 col-md-6">
                <label class="form-label">{{ __('Display name') }}</label>
                <input wire:model.defer="roleForm.display_name" type="text" class="form-control @error('roleForm.display_name') is-invalid @enderror" placeholder="اسم الدور">
                @error('roleForm.display_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>
              <div class="col-lg-6 col-12">
                <label class="form-label">{{ __('Description') }}</label>
                <input wire:model.defer="roleForm.description" type="text" class="form-control @error('roleForm.description') is-invalid @enderror" placeholder="{{ __('Short role description') }}">
                @error('roleForm.description')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-12">
                <label class="form-label">{{ __('Permissions') }}</label>
                <div class="row g-3 mb-3">
                  @foreach($projectPermissionPresets as $presetKey => $preset)
                    @php
                      $presetPermissions = $preset['permissions'] ?? [];
                      $selectedPresetPermissions = array_intersect($presetPermissions, $roleForm['permissions'] ?? []);
                      $isPresetActive = count($presetPermissions) > 0 && count($selectedPresetPermissions) === count($presetPermissions);
                    @endphp
                    <div class="col-xl-4 col-md-6">
                      <div class="project-preset {{ $isPresetActive ? 'is-active' : '' }}">
                        <div class="d-flex justify-content-between gap-3 mb-2">
                          <div>
                            <h6 class="mb-1">{{ $preset['label'] }}</h6>
                            <div class="text-muted small">{{ $preset['description'] }}</div>
                          </div>
                          <span class="badge bg-label-primary align-self-start">{{ count($selectedPresetPermissions) }}/{{ count($presetPermissions) }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                          <button wire:click="applyProjectPreset('{{ $presetKey }}')" type="button" class="btn btn-sm btn-primary">
                            <i class="ti ti-check me-1"></i>إضافة المشروع
                          </button>
                          <button wire:click="removeProjectPreset('{{ $presetKey }}')" type="button" class="btn btn-sm btn-label-secondary">
                            إزالة
                          </button>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>

                <div class="permission-checks">
                  @foreach($permissions->groupBy(fn($permission) => $permission->group_name ?: __('General')) as $groupName => $groupPermissions)
                    <div class="mb-3">
                      <div class="permission-group-title mb-2">
                        <div>
                          <h6>{{ $groupName }}</h6>
                          <div class="text-muted small">{{ $groupPermissions->count() }} صلاحية</div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                          <button wire:click="selectPermissionGroup(@js($groupName))" type="button" class="btn btn-xs btn-label-primary">
                            تحديد القسم
                          </button>
                          <button wire:click="clearPermissionGroup(@js($groupName))" type="button" class="btn btn-xs btn-label-secondary">
                            مسح القسم
                          </button>
                        </div>
                      </div>
                      <div class="row g-2">
                        @foreach($groupPermissions as $permission)
                          <div class="col-xl-3 col-md-4 col-sm-6 col-12">
                            <div class="form-check">
                              <input
                                wire:model.defer="roleForm.permissions"
                                class="form-check-input"
                                type="checkbox"
                                value="{{ $permission->name }}"
                                id="modalRolePermission{{ $permission->id }}"
                              >
                              <label class="form-check-label" for="modalRolePermission{{ $permission->id }}">
                                {{ $permission->display_name ?: $permission->name }}
                              </label>
                            </div>
                          </div>
                        @endforeach
                      </div>
                    </div>
                  @endforeach
                </div>
                @error('roleForm.permissions')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button wire:click="cancelEdit" type="button" class="btn btn-label-secondary">
              {{ __('Cancel') }}
            </button>
            <button wire:click="saveRole" type="button" class="btn btn-primary">
              <i class="ti ti-device-floppy me-1"></i>{{ $editingRoleId ? __('Save') : __('Create Role') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3">
      @foreach($roles as $role)
        <div class="col-xl-4 col-md-6">
          <div class="role-card">
            <div class="role-card-header d-flex justify-content-between gap-3 align-items-start">
              <div class="min-w-0">
                <h6 class="mb-1 text-truncate">{{ $role->display_name ?: $role->name }}</h6>
                <code>{{ $role->name }}</code>
              </div>
              <div class="text-end flex-shrink-0">
                @if($role->name !== 'Admin' || $this->canManagePrimaryAdminRole())
                  <button wire:click="editRole({{ $role->id }})" type="button" class="btn btn-sm btn-icon btn-label-info">
                    <i class="ti ti-pencil"></i>
                  </button>
                @endif
                @if(!in_array($role->name, ['Admin', 'Employee'], true))
                  <button wire:click="confirmDeleteRole({{ $role->id }})" type="button" class="btn btn-sm btn-icon btn-label-danger">
                    <i class="ti ti-trash"></i>
                  </button>
                @endif
              </div>
            </div>
            <div class="role-card-body">
              <div class="text-muted small">{{ $role->description ?: __('No description') }}</div>

              <div class="role-meta-grid">
                <div class="role-meta-item">
                  <small>{{ __('Users') }}</small>
                  <strong>{{ $role->users_count }}</strong>
                </div>
                <div class="role-meta-item">
                  <small>{{ __('Permissions') }}</small>
                  <strong>{{ $role->permissions->count() }}</strong>
                </div>
              </div>

              @if($role->permissions->count())
                <details class="role-permissions-more">
                  <summary>
                    <i class="ti ti-chevron-down toggle-icon"></i>
                    عرض الصلاحيات
                  </summary>
                  <div class="role-permissions-extra">
                    @foreach($role->permissions as $permission)
                      <span class="permission-chip">{{ $permission->display_name ?: $permission->name }}</span>
                    @endforeach
                  </div>
                </details>
              @else
                <div class="text-muted small mt-3">
                  {{ $role->name === 'Admin' ? __('Admin has full access automatically.') : __('No permissions assigned.') }}
                </div>
              @endif

              @if($blockedRoleId === $role->id)
                <div class="alert alert-warning mt-3 mb-0">
                  <div class="fw-semibold mb-1">لا يمكن حذف هذا الدور الآن</div>
                  <div class="small mb-2">
                    يوجد {{ $role->users_count }} مستخدم مرتبط بهذا الدور. غيّر دور المستخدمين أو احذف حساباتهم أولًا.
                  </div>
                  <a href="{{ route('settings-users', ['role' => $role->name]) }}" class="btn btn-sm btn-warning">
                    <i class="ti ti-users me-1"></i>عرض المستخدمين وتغيير الدور
                  </a>
                </div>
              @endif

              @if($confirmedRoleId === $role->id)
                <div class="mt-3">
                  <button wire:click="deleteRole" type="button" class="btn btn-sm btn-danger">
                    {{ __('Sure?') }}
                  </button>
                </div>
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
