<?php

namespace App\Actions\Fortify;

use App\Models\Contract;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $input['phone_country_code'] = Employee::normalizeCountryCode($input['phone_country_code'] ?? '971');
        $input['mobile_number'] = Employee::normalizeLocalPhone($input['mobile_number'] ?? '');
        $existingEmployee = Employee::find($input['employee_id'] ?? null);
        $fullPhoneNumber = Employee::combinePhoneNumber($input['phone_country_code'], $input['mobile_number']);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'employee_id' => ['required', 'integer', 'unique:users,employee_id'],
            'phone_country_code' => ['required', 'string', 'max:5'],
            'national_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('employees', 'national_number')->ignore($existingEmployee?->id),
            ],
            'mobile_number' => [
                'required',
                'string',
                'min:6',
                'max:15',
                'regex:/^[0-9]+$/',
                Rule::unique('employees', 'mobile_number')
                    ->where(fn ($query) => $query->where('phone_country_code', $input['phone_country_code']))
                    ->ignore($existingEmployee?->id),
            ],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'job_title' => ['required', 'string', 'max:255'],
            'job_specialization' => ['required', 'string', 'max:255'],
            'nationality' => ['required', 'string', 'max:255'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->after(function ($validator) use ($fullPhoneNumber, $existingEmployee) {
            $existingUserId = $existingEmployee?->user?->id;

            $phoneExists = User::query()
                ->where('mobile', $fullPhoneNumber)
                ->when($existingUserId, fn ($query) => $query->where('id', '!=', $existingUserId))
                ->exists();

            if ($phoneExists) {
                $validator->errors()->add('mobile_number', __('The phone number has already been taken.'));
            }
        })->validate();

        $defaultContractId = Contract::query()->value('id');

        if (! $defaultContractId) {
            Validator::make([], [])->after(function ($validator) {
                $validator->errors()->add('employee_id', __('No contract records found. Please contact the administrator.'));
            })->validate();
        }

        return DB::transaction(function () use ($existingEmployee, $input, $defaultContractId) {
            $fullName = trim($input['name']);

            if ($existingEmployee) {
                $existingEmployee->update([
                    'first_name' => $existingEmployee->first_name ?: $fullName,
                    'father_name' => $existingEmployee->father_name ?: '',
                    'last_name' => $existingEmployee->last_name ?: '',
                    'mother_name' => $existingEmployee->mother_name ?: '',
                    'birth_and_place' => $existingEmployee->birth_and_place ?: '',
                    'national_number' => $input['national_number'],
                    'phone_country_code' => $input['phone_country_code'],
                    'mobile_number' => $input['mobile_number'],
                    'degree' => $input['job_specialization'],
                    'nationality' => $input['nationality'],
                    'job_title' => $input['job_title'],
                    'job_specialization' => $input['job_specialization'],
                ]);

                $employee = $existingEmployee->fresh();
            } else {
                $employee = Employee::create([
                    'id' => $input['employee_id'],
                    'contract_id' => $defaultContractId,
                    'first_name' => $fullName,
                    'father_name' => '',
                    'last_name' => '',
                    'mother_name' => '',
                    'birth_and_place' => '',
                    'national_number' => $input['national_number'],
                    'phone_country_code' => $input['phone_country_code'],
                    'mobile_number' => $input['mobile_number'],
                    'degree' => $input['job_specialization'],
                    'gender' => 1,
                    'address' => '-',
                    'nationality' => $input['nationality'],
                    'job_title' => $input['job_title'],
                    'job_specialization' => $input['job_specialization'],
                    'profile_photo_path' => 'profile-photos/.default-photo.jpg',
                ]);
            }

            $user = User::create([
                'name' => $input['name'],
                'employee_id' => $employee->id,
                'mobile' => $fullPhoneNumber,
                'username' => (string) $employee->id,
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'visible_password' => $input['password'],
                'profile_photo_path' => 'profile-photos/.default-photo.jpg',
            ]);

            $user->assignRole(Role::firstOrCreate([
                'name' => 'Employee',
                'guard_name' => 'web',
            ]));

            return $user;
        });
    }
}
