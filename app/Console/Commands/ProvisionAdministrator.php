<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class ProvisionAdministrator extends Command
{
    protected $signature = 'admin:provision {email? : The administrator email address}';

    protected $description = 'Securely create an administrator account';

    public function handle(): int
    {
        $email = mb_strtolower((string) ($this->argument('email') ?: text(
            label: 'Administrator email address',
            required: true,
            validate: ['email' => ['email:rfc', 'max:255']],
        )));

        if (User::query()->where('email', $email)->exists()) {
            $this->error('An account with that email address already exists. No changes were made.');

            return self::FAILURE;
        }

        $name = text(
            label: 'Administrator name',
            required: true,
            validate: ['name' => ['string', 'max:255']],
        );

        $plainPassword = password(
            label: 'Password (at least 12 characters)',
            required: true,
        );

        try {
            validator(
                ['password' => $plainPassword],
                ['password' => ['required', Password::min(12)->letters()->numbers()->symbols()]],
            )->validate();
        } catch (ValidationException $exception) {
            $this->error($exception->validator->errors()->first('password'));

            return self::FAILURE;
        }

        if (! confirm('Create this administrator account?', default: false)) {
            $this->warn('Administrator provisioning cancelled.');

            return self::FAILURE;
        }

        $administrator = new User([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($plainPassword),
        ]);
        $administrator->role = UserRole::Administrator;
        $administrator->email_verified_at = now();
        $administrator->save();

        $this->info('Administrator account created successfully.');

        return self::SUCCESS;
    }
}
