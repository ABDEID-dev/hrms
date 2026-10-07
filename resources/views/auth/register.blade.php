@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', __('ui.register_page'))

@section('page-style')
{{-- Page Css files --}}
<link rel="stylesheet" href="{{ asset(mix('assets/vendor/css/pages/page-auth.css')) }}">
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover authentication-bg">
  <div class="authentication-inner row">
    <!-- /Left Text -->
    <div class="d-none d-lg-flex col-lg-7 p-0">
      <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
        <img src="{{ asset('assets/img/illustrations/auth-register-illustration-'.$configData['style'].'.png') }}" alt="auth-register-cover" class="img-fluid my-5 auth-illustration" data-app-light-img="illustrations/auth-register-illustration-light.png" data-app-dark-img="illustrations/auth-register-illustration-dark.png">

        <img src="{{ asset('assets/img/illustrations/bg-shape-image-'.$configData['style'].'.png') }}" alt="auth-register-cover" class="platform-bg" data-app-light-img="illustrations/bg-shape-image-light.png" data-app-dark-img="illustrations/bg-shape-image-dark.png">
      </div>
    </div>
    <!-- /Left Text -->

    <!-- Register -->
    <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4">
      <div class="w-px-400 mx-auto">
        <!-- Logo -->
        <div class="app-brand mb-4">
          <a href="{{url('/')}}" class="app-brand-link gap-2">
            <span class="app-brand-logo demo">@include('_partials.macros',["height"=>20,"withbg"=>'fill: #fff;'])</span>
          </a>
        </div>
        <!-- /Logo -->
        <h3 class="mb-1">Adventure starts here 🚀</h3>
        <p class="mb-4">Make your app management easy and fun!</p>

        <form id="formAuthentication" class="mb-3" action="{{ route('register') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label for="employee_id" class="form-label">{{ __('ui.employee_id') }}</label>
            <input type="number" class="form-control @error('employee_id') is-invalid @enderror" id="employee_id" name="employee_id" placeholder="1001" value="{{ old('employee_id') }}" />
            @error('employee_id')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>
          <div class="mb-3">
            <label for="username" class="form-label">{{ __('ui.full_name') }}</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="username" name="name" placeholder="John Doe" autofocus value="{{ old('name') }}" />
            @error('name')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>
          <div class="mb-3">
            <label for="national_number" class="form-label">{{ __('National Number') }}</label>
            <input type="text" class="form-control @error('national_number') is-invalid @enderror" id="national_number" name="national_number" placeholder="02000000000" value="{{ old('national_number') }}" />
            @error('national_number')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>
          <div class="mb-3">
            <label for="mobile_number" class="form-label">{{ __('ui.phone_number') }}</label>
            <div class="input-group">
              <select class="form-select @error('phone_country_code') is-invalid @enderror" id="phone_country_code" name="phone_country_code" style="max-width: 180px;">
                <option value="971" @selected(old('phone_country_code', '971') === '971')>{{ __('ui.uae') }}</option>
                <option value="966" @selected(old('phone_country_code') === '966')>{{ __('ui.saudi_arabia') }}</option>
                <option value="973" @selected(old('phone_country_code') === '973')>{{ __('ui.bahrain') }}</option>
                <option value="965" @selected(old('phone_country_code') === '965')>{{ __('ui.kuwait') }}</option>
                <option value="968" @selected(old('phone_country_code') === '968')>{{ __('ui.oman') }}</option>
                <option value="974" @selected(old('phone_country_code') === '974')>{{ __('ui.qatar') }}</option>
                <option value="962" @selected(old('phone_country_code') === '962')>{{ __('ui.jordan') }}</option>
                <option value="20" @selected(old('phone_country_code') === '20')>{{ __('ui.egypt') }}</option>
                <option value="963" @selected(old('phone_country_code') === '963')>{{ __('ui.syria') }}</option>
              </select>
              <input type="text" class="form-control @error('mobile_number') is-invalid @enderror" id="mobile_number" name="mobile_number" placeholder="501234567" value="{{ old('mobile_number') }}" inputmode="numeric" />
            </div>
            @error('phone_country_code')
            <span class="invalid-feedback d-block" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
            @error('mobile_number')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input type="text" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="john@example.com" value="{{ old('email') }}" />
            @error('email')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>
          <div class="mb-3">
            <label for="job_title" class="form-label">{{ __('ui.job_title') }}</label>
            <input type="text" class="form-control @error('job_title') is-invalid @enderror" id="job_title" name="job_title" placeholder="HR Officer" value="{{ old('job_title') }}" />
            @error('job_title')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>
          <div class="mb-3">
            <label for="job_specialization" class="form-label">{{ __('ui.job_specialization') }}</label>
            <input type="text" class="form-control @error('job_specialization') is-invalid @enderror" id="job_specialization" name="job_specialization" placeholder="Human Resources" value="{{ old('job_specialization') }}" />
            @error('job_specialization')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>
          <div class="mb-3">
            <label for="nationality" class="form-label">{{ __('ui.nationality') }}</label>
            <input type="text" class="form-control @error('nationality') is-invalid @enderror" id="nationality" name="nationality" placeholder="Syrian" value="{{ old('nationality') }}" />
            @error('nationality')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>
          <div class="mb-3 form-password-toggle">
            <label class="form-label" for="password">{{ __('Password') }}</label>
            <div class="input-group input-group-merge @error('password') is-invalid @enderror">
              <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
              <span class="input-group-text cursor-pointer">
                <i class="ti ti-eye-off"></i>
              </span>
            </div>
            @error('password')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>

          <div class="mb-3 form-password-toggle">
            <label class="form-label" for="password-confirm">{{ __('Confirm Password') }}</label>
            <div class="input-group input-group-merge">
              <input type="password" id="password-confirm" class="form-control" name="password_confirmation" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
              <span class="input-group-text cursor-pointer">
                <i class="ti ti-eye-off"></i>
              </span>
            </div>
          </div>
          @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
            <div class="mb-3">
              <div class="form-check @error('terms') is-invalid @enderror">
                <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" id="terms" name="terms" />
                <label class="form-check-label" for="terms">
                  I agree to the
                  <a href="{{ route('policy.show') }}" target="_blank">privacy policy</a> &
                  <a href="{{ route('terms.show') }}" target="_blank">terms</a>
                </label>
              </div>
              @error('terms')
                <div class="invalid-feedback" role="alert">
                    <span class="fw-medium">{{ $message }}</span>
                </div>
              @enderror
            </div>
          @endif
          <button type="submit" class="btn btn-primary d-grid w-100">{{ __('ui.sign_up') }}</button>
        </form>

        <p class="text-center mt-2">
          <span>{{ __('ui.already_have_account') }}</span>
          @if (Route::has('login'))
          <a href="{{ route('login') }}">
            <span>{{ __('ui.sign_in_instead') }}</span>
          </a>
          @endif
        </p>

        <div class="divider my-4">
          <div class="divider-text">or</div>
        </div>

        <div class="d-flex justify-content-center">
          <a href="javascript:;" class="btn btn-icon btn-label-facebook me-3">
            <i class="tf-icons fa-brands fa-facebook-f fs-5"></i>
          </a>

          <a href="javascript:;" class="btn btn-icon btn-label-google-plus me-3">
            <i class="tf-icons fa-brands fa-google fs-5"></i>
          </a>

          <a href="javascript:;" class="btn btn-icon btn-label-twitter">
            <i class="tf-icons fa-brands fa-twitter fs-5"></i>
          </a>
        </div>
      </div>
    </div>
    <!-- /Register -->
  </div>
</div>
@endsection
