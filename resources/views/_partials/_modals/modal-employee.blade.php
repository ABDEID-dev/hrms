@push('custom-css')
  <style>
    input::-webkit-outer-spin-button,
      input::-webkit-inner-spin-button {
          -webkit-appearance: none;
          margin: 0;
      }

    input[type="number"] {
        -moz-appearance: textfield;
    }
  </style>
@endpush

<div wire:ignore.self class="modal fade" id="employeeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-simple">
    <div class="modal-content p-0 p-md-5">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3 class="mb-2"></h3>
          <h3 class="mb-2">{{ $isEdit ? __('Update Employee') : __('New Employee') }}</h3>
          <p class="text-muted">{{ __('Please fill out the following information') }}</p>
        </div>
        <form wire:submit="submitEmployee" class="row g-3">
          @php
            $employeePhotoUrl = $isEdit && $employee
              ? route('employee-profile-photo', ['employee' => $employee->id, 'v' => optional($employee->updated_at)->timestamp])
              : asset('storage/profile-photos/.default-photo.jpg');
          @endphp
          <div class="col-12 mb-3">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 p-3 rounded border">
              <div class="d-flex align-items-center gap-3">
                <img
                  src="{{ $profilePhoto ? $profilePhoto->temporaryUrl() : $employeePhotoUrl }}"
                  alt="{{ __('Employee photo') }}"
                  class="rounded-circle"
                  style="width: 84px; height: 84px; object-fit: cover;"
                >
                <div>
                  <div class="fw-semibold">{{ __('Employee Photo') }}</div>
                  <small class="text-muted">{{ __('Optional profile photo shown in the employee avatar and account menu.') }}</small>
                  @error('profilePhoto')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              <div class="text-center text-md-end">
                <input wire:model="profilePhoto" id="employeeProfilePhotoUpload" type="file" class="d-none" accept="image/*">
                <label for="employeeProfilePhotoUpload" class="btn btn-label-primary mb-1">
                  <i class="ti ti-camera me-1"></i>{{ __('Choose Photo') }}
                </label>
                @if($profilePhoto)
                  <div class="small text-success">{{ __('New photo selected') }}</div>
                @endif
                <div wire:loading wire:target="profilePhoto" class="small text-muted">{{ __('Uploading...') }}</div>
              </div>
            </div>
          </div>
          <div class="col-md-3 col-12 mb-4">
            <label class="form-label">{{ __('ID') }}</label>
            <input wire:model='employeeInfo.id' class="form-control @error('employeeInfo.id') is-invalid @enderror" type="number" readonly/>
            <small class="text-muted d-block mt-1">يتم توليد رقم المعرف تلقائيًا.</small>
            @error('employeeInfo.id')
              <div class="invalid-feedback d-block">
                {{ $message }}
              </div>
            @enderror
          </div>
          <div class="col-md-3 col-12 mb-4">
            <label class="form-label w-100" for="employeeInfo.contractId" class="form-label">{{ __('Contract ID') }}</label>
            <select wire:model.defer="employeeInfo.contractId" class="form-select @error('employeeInfo.contractId') is-invalid @enderror" id="employeeInfo.contractId">
              <option value=""></option>
              @foreach($contracts as $contract)
              <option value="{{ $contract->id }}">{{ $contract->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3 col-12 mb-4">
            <label class="form-label w-100" for="nationalNumber">{{ __('Emirates ID / Passport Number') }}</label>
            <input wire:model.defer="employeeInfo.nationalNumber" class="form-control @error('employeeInfo.nationalNumber') is-invalid @enderror" id="employeeInfo.nationalNumber" placeholder="784-0000-0000000-0 / A12345678" type="text" maxlength="20">
            <small class="text-muted d-block mt-1">{{ __('Use UAE Emirates ID format 784-0000-0000000-0, or enter the passport number.') }}</small>
            @error('employeeInfo.nationalNumber')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
          </div>
          <div class="col-md-3 col-12 mb-4">
            <label class="form-label w-100" for="mobile">{{ __('Mobile') }}</label>
            <div class="input-group">
              <select wire:model.defer="employeeInfo.phoneCountryCode" class="form-select @error('employeeInfo.phoneCountryCode') is-invalid @enderror" style="max-width: 90px;">
                <option value="971">+971</option>
                <option value="966">+966</option>
                <option value="973">+973</option>
                <option value="965">+965</option>
                <option value="968">+968</option>
                <option value="974">+974</option>
                <option value="962">+962</option>
                <option value="20">+20</option>
                <option value="963">+963</option>
                <option value="212">+212</option>
                <option value="216">+216</option>
                <option value="213">+213</option>
              </select>
              <input wire:model.defer="employeeInfo.mobileNumber" class="form-control @error('employeeInfo.mobileNumber') is-invalid @enderror" id="employeeInfo.mobile" placeholder="501234567" type="text" maxlength="15" inputmode="numeric">
            </div>
            @error('employeeInfo.mobileNumber')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
            @error('employeeInfo.phoneCountryCode')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
            @enderror
          </div>
          <div class="col-md-6 col-12 mb-4">
            <label class="form-label w-100">{{ __('ui.full_name') }}</label>
            <input wire:model.defer="employeeInfo.fullName" class="form-control @error('employeeInfo.fullName') is-invalid @enderror" type="text" />
            @error('employeeInfo.fullName')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
          </div>
          <div class="col-md-4 col-12 mb-4">
            <label class="form-label w-100" for="employeeInfo.age">{{ __('ui.age') }}</label>
            <input wire:model.defer="employeeInfo.age" type="number" min="1" max="120" step="1" inputmode="numeric" class="form-control @error('employeeInfo.age') is-invalid @enderror" id="employeeInfo.age">
            @error('employeeInfo.age')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
          </div>
          <div class="col-md-4 col-12 mb-4">
            <label class="form-label w-100" for="employeeInfo.gender" class="form-label">{{ __('Gender') }}</label>
            <select  wire:model.defer="employeeInfo.gender" @error('employeeInfo.gender') is-invalid @enderror id="employeeInfo.gender" class="form-select">
              <option value=""></option>
              <option value="1">{{ __('Male') }}</option>
              <option value="0">{{ __('Female') }}</option>
            </select>
          </div>
          <div class="col-md-5 col-12 mb-4">
            <label class="form-label w-100" for="employeeInfo.username">{{ __('Username') }}</label>
            <input wire:model.defer="employeeInfo.username" type="text" class="form-control @error('employeeInfo.username') is-invalid @enderror" id="employeeInfo.username">
            @error('employeeInfo.username')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
          </div>
          <div class="col-md-5 col-12 mb-4">
            <label class="form-label w-100" for="employeeInfo.password">{{ __('Password') }}</label>
            <input wire:model.defer="employeeInfo.password" type="text" class="form-control @error('employeeInfo.password') is-invalid @enderror" id="employeeInfo.password" placeholder="{{ $isEdit ? __('Leave blank to keep current password') : '********' }}">
            @error('employeeInfo.password')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
          </div>
          @if($this->canAssignEmployeePermissions())
          <div class="col-md-4 col-12 mb-4">
            <label class="form-label w-100" for="employeeInfo.accessRole">{{ __('Role') }}</label>
            <select wire:model.live="employeeInfo.accessRole" class="form-select @error('employeeInfo.accessRole') is-invalid @enderror" id="employeeInfo.accessRole">
              @foreach($availableRoles as $role)
                <option value="{{ $role->name }}">{{ $role->display_name ?: __($role->name) }}</option>
              @endforeach
            </select>
            <small class="text-muted d-block mt-1">الدور يحدد ماذا يستطيع الموظف أن يفعل داخل النظام.</small>
            @error('employeeInfo.accessRole')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
          </div>
          @else
            <div class="col-12 mb-4">
              <div class="alert alert-info mb-0">
                الصلاحيات والأدوار لا يمكن تعديلها من هنا إلا بواسطة الأدمن.
              </div>
            </div>
          @endif
          <div class="col-md-4 col-12 mb-4">
            <label class="form-label w-100" for="employeeInfo.requiresAttendanceLocation">موقع البصمة</label>
            <select wire:model.defer="employeeInfo.requiresAttendanceLocation" class="form-select @error('employeeInfo.requiresAttendanceLocation') is-invalid @enderror" id="employeeInfo.requiresAttendanceLocation">
              <option value="1">مطلوب تحديد الموقع</option>
              <option value="0">غير مطلوب تحديد الموقع</option>
            </select>
            <small class="text-muted d-block mt-1">اختر "غير مطلوب" للأجهزة التي لا يعمل عليها GPS بشكل صحيح.</small>
            @error('employeeInfo.requiresAttendanceLocation')
              <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>
          <div class="col-md-4 col-12 mb-4">
            <label class="form-label w-100" for="employeeInfo.nationality">{{ __('ui.nationality') }}</label>
            <input wire:model.defer="employeeInfo.nationality" type="text" class="form-control @error('employeeInfo.nationality') is-invalid @enderror" id="employeeInfo.nationality">
          </div>
          <div class="col-md-4 col-12 mb-4">
            <label class="form-label w-100" for="employeeInfo.jobTitle">{{ __('ui.job_title') }}</label>
            <input wire:model.defer="employeeInfo.jobTitle" type="text" class="form-control @error('employeeInfo.jobTitle') is-invalid @enderror" id="employeeInfo.jobTitle">
          </div>
          <div class="col-md-4 col-12 mb-4">
            <label class="form-label w-100" for="employeeInfo.jobTitleEn">Job Title (EN)</label>
            <input wire:model.defer="employeeInfo.jobTitleEn" type="text" dir="ltr" class="form-control @error('employeeInfo.jobTitleEn') is-invalid @enderror" id="employeeInfo.jobTitleEn">
          </div>
          <div class="col-md-4 col-12 mb-4">
            <label class="form-label w-100" for="employeeInfo.jobSpecialization">{{ __('ui.job_specialization') }}</label>
            <input wire:model.defer="employeeInfo.jobSpecialization" type="text" class="form-control @error('employeeInfo.jobSpecialization') is-invalid @enderror" id="employeeInfo.jobSpecialization">
          </div>
          <div class="col-md-4 col-12 mb-4">
            <label class="form-label w-100" for="employeeInfo.basicSalary">{{ __('ui.basic_salary') }}</label>
            <input wire:model.defer="employeeInfo.basicSalary" type="number" step="0.01" min="0" class="form-control @error('employeeInfo.basicSalary') is-invalid @enderror" id="employeeInfo.basicSalary">
          </div>
          <div class="col-md-4 col-12 mb-4">
            <label class="form-label w-100" for="employeeInfo.housingAllowance">{{ __('ui.housing_allowance') }}</label>
            <input wire:model.defer="employeeInfo.housingAllowance" type="number" step="0.01" min="0" class="form-control @error('employeeInfo.housingAllowance') is-invalid @enderror" id="employeeInfo.housingAllowance">
          </div>
          <div class="col-md-4 col-12 mb-4">
            <label class="form-label w-100" for="employeeInfo.transportationAllowance">{{ __('ui.transportation_allowance') }}</label>
            <input wire:model.defer="employeeInfo.transportationAllowance" type="number" step="0.01" min="0" class="form-control @error('employeeInfo.transportationAllowance') is-invalid @enderror" id="employeeInfo.transportationAllowance">
          </div>
          <div class="col-md-4 col-12 mb-4">
            <label class="form-label w-100" for="employeeInfo.visaType">{{ __('ui.visa_type') }}</label>
            <select wire:model.defer="employeeInfo.visaType" class="form-select @error('employeeInfo.visaType') is-invalid @enderror" id="employeeInfo.visaType">
              <option value=""></option>
              <option value="external_residency">{{ __('ui.external_residency') }}</option>
              <option value="internal_residency">{{ __('ui.internal_residency') }}</option>
              <option value="temporary_visit">{{ __('ui.temporary_visit') }}</option>
            </select>
          </div>

          <div class="col-12 mb-4">
            <label class="form-label w-100">{{ __('Note') }}</label>
            <input wire:model='employeeInfo.notes' class="form-control @error('employeeInfo.notes') is-invalid @enderror" type="text" />
          </div>

          <div class="col-12 mb-4">
            <div class="alert alert-info mb-0">
              صلاحيات الموظف تدار من صفحة <strong>الأدوار والصلاحيات</strong>. هنا يتم اختيار الدور فقط حتى لا يحدث تضارب بين صلاحيات الموظف وصلاحيات الدور.
            </div>
          </div>

          <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary me-sm-3 me-1">{{ __('Submit') }}</button>
            <button type="reset" class="btn btn-label-secondary btn-reset" data-bs-dismiss="modal" aria-label="Close">{{ __('Cancel') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@push('custom-scripts')

@endpush
