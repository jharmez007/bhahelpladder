<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class ProvisionCounsellor extends Command
{
    protected $signature = 'counsellor:provision {email? : The counsellor email address}';

    protected $description = 'Securely create a counsellor account and booking profile';

    public function handle(): int
    {
        $email = mb_strtolower((string) ($this->argument('email') ?: text(
            label: 'Counsellor email address',
            required: true,
            validate: ['email' => ['email:rfc', 'max:255']],
        )));

        if (User::query()->where('email', $email)->exists()) {
            $this->error('An account with that email address already exists. No changes were made.');

            return self::FAILURE;
        }

        $name = text(label: 'Counsellor name', required: true, validate: ['name' => ['string', 'max:255']]);
        $plainPassword = password(label: 'Password (at least 12 characters)', required: true);

        try {
            validator(
                ['password' => $plainPassword],
                ['password' => ['required', Password::min(12)->letters()->numbers()->symbols()]],
            )->validate();
        } catch (ValidationException $exception) {
            $this->error($exception->validator->errors()->first('password'));

            return self::FAILURE;
        }

        if (! confirm('Create this counsellor account?', default: false)) {
            $this->warn('Counsellor provisioning cancelled.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($email, $name, $plainPassword): void {
            $counsellor = new User([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($plainPassword),
            ]);
            $counsellor->role = UserRole::Counsellor;
            $counsellor->email_verified_at = now();
            $counsellor->save();
            $counsellor->counsellorProfile()->create(['is_active' => true]);
        });

        $this->info('Counsellor account and booking profile created successfully.');

        return self::SUCCESS;
    }
}
