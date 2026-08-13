<?php

namespace App\Console\Commands;

use App\Support\PasswordHash;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SetUserPassword extends Command
{
    /**
     * There is deliberately no --password= option: a plaintext password passed
     * as an argument would land in shell history and in the process list. The
     * value is only ever read through a hidden prompt.
     *
     * @var string
     */
    protected $signature = 'user:set-password
                            {email : Email address of the account to update}
                            {--force : Skip the confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set a user password from a hidden prompt, hashed with the application default driver';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        // DB::table rather than Eloquent on purpose: it bypasses the User
        // model's 'password' => 'hashed' cast, which throws "Could not verify
        // the hashed value's configuration" when the row currently holds a
        // non-bcrypt hash — exactly the situation this command exists to repair.
        $user = DB::table('users')->where('email', $email)->first();

        if (!$user) {
            $this->error("No user found with email {$email}.");

            return self::FAILURE;
        }

        $this->line("Account   : {$user->name} (id {$user->id}, role {$user->role})");
        $this->line('Stored as : ' . PasswordHash::describe($user->password));
        $this->line('Will use  : ' . config('hashing.driver'));
        $this->newLine();

        if (!$this->option('force') && !$this->confirm("Set a new password for {$email}?", false)) {
            $this->warn('Aborted. Nothing was changed.');

            return self::FAILURE;
        }

        $password = (string) $this->secret('New password (input hidden)');
        $confirm  = (string) $this->secret('Confirm new password');

        if ($password === '') {
            $this->error('Password cannot be empty. Nothing was changed.');

            return self::FAILURE;
        }

        if (!hash_equals($password, $confirm)) {
            $this->error('Passwords do not match. Nothing was changed.');

            return self::FAILURE;
        }

        // Mirrors the registration rule in RegisterController::store().
        if (strlen($password) < 12) {
            $this->error('Password must be at least 12 characters. Nothing was changed.');

            return self::FAILURE;
        }

        DB::table('users')->where('id', $user->id)->update([
            'password'   => Hash::make($password),
            'updated_at' => now(),
        ]);

        $fresh = DB::table('users')->where('id', $user->id)->value('password');

        $this->newLine();
        $this->info("Password updated for {$email}.");
        $this->info('Now stored as: ' . PasswordHash::describe($fresh));
        $this->warn('The plaintext was never written to disk or to shell history. Deliver it out of band.');

        return self::SUCCESS;
    }
}
