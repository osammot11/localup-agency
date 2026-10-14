<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

#[Signature('admin:create')]
#[Description('Crea un account amministratore per consultare i contatti delle landing page')]
class CreateAdminUser extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = trim((string) $this->ask('Nome'));
        $email = trim((string) $this->ask('Email'));
        $password = (string) $this->secret('Password (almeno 12 caratteri)');
        $confirmation = (string) $this->secret('Conferma password');

        $validation = Validator::make(compact('name', 'email', 'password', 'confirmation'), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12'],
            'confirmation' => ['same:password'],
        ]);

        if ($validation->fails()) {
            foreach ($validation->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $admin = new User;
        $admin->name = $name;
        $admin->email = $email;
        $admin->password = $password;
        $admin->is_admin = true;
        $admin->save();

        $this->info('Account amministratore creato. Accedi da /admin/login.');

        return self::SUCCESS;
    }
}
