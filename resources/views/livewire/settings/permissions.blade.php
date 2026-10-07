<div>
  @section('title', __('Permissions'))

  @section('page-style')
    <style>
      .permissions-page .toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
      }

      .permissions-page .permission-group {
        border: 1px solid rgba(var(--bs-border-color-rgb), .45);
        border-radius: .5rem;
        overflow: hidden;
        background: rgba(var(--bs-body-bg-rgb), .24);
      }

      .permissions-page .permission-group-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        background: rgba(var(--bs-body-bg-rgb), .48);
        padding: .9rem 1rem;
        border-bottom: 1px solid rgba(var(--bs-border-color-rgb), .35);
      }

      .permissions-page .permission-row {
        display: grid;
        grid-template-columns: minmax(220px, 1fr) minmax(220px, .85fr) auto;
        gap: 1rem;
        align-items: center;
        padding: .95rem 1rem;
        border-bottom: 1px solid rgba(var(--bs-border-color-rgb), .24);
      }

      .permissions-page .permission-row:last-child {
        border-bottom: 0;
      }

      .permissions-page .permission-code {
        direction: ltr;
        text-align: left;
        unicode-bidi: isolate;
        white-space: normal;
        overflow-wrap: anywhere;
      }

      .permissions-page .permission-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: .35rem;
      }

      .permissions-page .permission-meta {
        color: var(--bs-secondary-color);
        font-size: .78rem;
      }

      @media (max-width: 767.98px) {
        .permissions-page {
          margin-inline: -.75rem;
        }

        .permissions-page .card,
        .permissions-page .permission-group {
          border-radius: 0;
        }

        .permissions-page .permission-row {
          grid-template-columns: 1fr;
          gap: .55rem;
        }

        .permissions-page .permission-actions {
          justify-content: flex-start;
        }

        .permissions-page .toolbar .form-control {
          width: 100% !important;
        }
      }
    </style>
  @endsection

  @include('_partials/_alerts/alert-general')

  <div class="permissions-page">
    <div class="card mb-4">
      <div class="card-header border-bottom">
        <div class="toolbar">
          <div>
            <h5 class="mb-1">الصلاحيات</h5>
            <small class="text-muted">كتالوج الصلاحيات الرسمي والمخصص، مرتب حسب القسم.</small>
          </div>
          <div class="d-flex flex-wrap gap-2">
            <input
              wire:model.live.debounce.300ms="searchTerm"
              type="text"
              class="form-control"
              style="width: 280px;"
              placeholder="بحث باسم الصلاحية أو القسم"
            >
            <button wire:click="syncSystemPermissions" type="button" class="btn btn-label-primary">
              <i class="ti ti-refresh me-1"></i>
              مزامنة النظام
            </button>
          </div>
        </div>
      </div>

      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-lg-4 col-md-6">
            <label class="form-label">اسم الصلاحية داخل النظام</label>
            <input
              wire:model.defer="permissionForm.name"
              type="text"
              dir="ltr"
              class="form-control @error('permissionForm.name') is-invalid @enderror"
              placeholder="edit account revenues"
            >
            @error('permissionForm.name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-4 col-md-6">
            <label class="form-label">الاسم الظاهر للمستخدم</label>
            <input
              wire:model.defer="permissionForm.display_name"
              type="text"
              class="form-control @error('permissionForm.display_name') is-invalid @enderror"
              placeholder="تعديل إيرادات اليوم الحالي"
            >
            @error('permissionForm.display_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-3 col-md-6">
            <label class="form-label">القسم</label>
            <input
              wire:model.defer="permissionForm.group_name"
              type="text"
              class="form-control @error('permissionForm.group_name') is-invalid @enderror"
              placeholder="الحسابات"
            >
            @error('permissionForm.group_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="col-lg-1 col-md-6">
            <button wire:click="savePermission" type="button" class="btn btn-primary w-100" title="{{ $editingPermissionId ? 'حفظ' : 'إضافة' }}">
              <i class="ti {{ $editingPermissionId ? 'ti-device-floppy' : 'ti-plus' }}"></i>
            </button>
          </div>
          @if($editingPermissionId)
            <div class="col-12">
              <button wire:click="cancelEdit" type="button" class="btn btn-label-secondary">
                {{ __('Cancel') }}
              </button>
            </div>
          @endif
        </div>
      </div>
    </div>

    @forelse($permissions->groupBy(fn($permission) => $permission->group_name ?: __('General')) as $groupName => $groupPermissions)
      <div class="permission-group mb-3">
        <div class="permission-group-header">
          <div>
            <h6 class="mb-1">{{ $groupName }}</h6>
            <div class="permission-meta">{{ $groupPermissions->count() }} صلاحية</div>
          </div>
          <span class="badge bg-label-primary">{{ $groupPermissions->where('is_system', true)->count() }} نظامية</span>
        </div>

        <div>
          @foreach($groupPermissions as $permission)
            <div class="permission-row">
              <div>
                <div class="fw-semibold">{{ $permission->display_name ?: $permission->name }}</div>
                <div class="permission-meta">
                  {{ $permission->is_system ? 'صلاحية نظام مرتبطة بالكود' : 'صلاحية مخصصة' }}
                </div>
              </div>

              <div>
                <code class="permission-code">{{ $permission->name }}</code>
                <div class="permission-meta">اسم النظام</div>
              </div>

              <div class="permission-actions">
                <button wire:click="editPermission({{ $permission->id }})" type="button" class="btn btn-sm btn-icon btn-label-info" title="تعديل">
                  <i class="ti ti-pencil"></i>
                </button>

                @unless($permission->is_system)
                  <button wire:click="confirmDeletePermission({{ $permission->id }})" type="button" class="btn btn-sm btn-icon btn-label-danger" title="حذف">
                    <i class="ti ti-trash"></i>
                  </button>
                @endunless

                @if($confirmedPermissionId === $permission->id)
                  <button wire:click="deletePermission" type="button" class="btn btn-xs btn-danger">
                    {{ __('Sure?') }}
                  </button>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @empty
      <div class="card">
        <div class="card-body text-center text-muted py-5">{{ __('No data found') }}</div>
      </div>
    @endforelse
  </div>
</div>
