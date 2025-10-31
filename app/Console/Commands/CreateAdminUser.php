<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin 
        {email : Email for the admin user}
        {--name= : Display name}
        {--password= : Plain-text password}
        {--telephone= : Optional telephone number}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user with the given credentials';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = strtolower($this->argument('email'));

        if (User::where('email', $email)->exists()) {
            $this->error("A user with email {$email} already exists.");

            return self::FAILURE;
        }

        $name = $this->option('name') ?: $this->ask('Name');
        $password = $this->option('password') ?: $this->secret('Password');
        $telephone = $this->option('telephone') ?: null;

        if (!$password) {
            $this->error('Password is required.');

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name ?: Str::title(Str::before($email, '@')),
            'email' => $email,
            'password_hash' => Hash::make($password),
            'role' => 'Admin',
            'status' => 'active',
            'telephone' => $telephone,
            'email_verified_at' => now(),
        ]);

        $this->info("Admin user {$user->email} created with id {$user->id}.");

        return self::SUCCESS;
    }
}
