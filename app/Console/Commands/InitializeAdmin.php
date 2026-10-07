<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class InitializeAdmin extends Command
{
    protected $signature = 'abdeid:create-admin';

    protected $description = 'Create an administrator interactively without a default password';

    public function handle(): int
    {
        $name = $this->ask('Administrator name', 'ABDEID');
        $email = $this->ask('Administrator email');
        $password = $this->secret('Password (at least 12 characters)');
        $confirmation = $this->secret('Confirm password');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $confirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);
        $user->assignRole(Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']));
        $this->info('Administrator created.');

        return self::SUCCESS;
    }
}
