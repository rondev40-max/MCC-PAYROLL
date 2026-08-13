<?php

namespace App\Console\Commands;

use App\Support\PasswordHash;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UserHashInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:hash-info
                            {email? : Limit the report to a single email address}
                            {--bad : Only list accounts that are not on the current default hash}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Report the password hash algorithm stored for each user (read-only)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Default hash driver : ' . config('hashing.driver'));
        $this->info('Bcrypt rounds       : ' . config('hashing.bcrypt.rounds'));
        $this->info('argon2i available   : ' . (defined('PASSWORD_ARGON2I') ? 'yes' : 'NO'));
        $this->info('argon2id available  : ' . (defined('PASSWORD_ARGON2ID') ? 'yes' : 'NO'));
        $this->newLine();

        $query = DB::table('users')
            ->select('id', 'email', 'role', 'password')
            ->orderBy('id');

        if ($email = $this->argument('email')) {
            $query->where('email', $email);
        }

        $rows = [];
        $bad  = 0;

        foreach ($query->cursor() as $user) {
            $needsUpgrade = PasswordHash::needsUpgrade($user->password);

            if ($needsUpgrade) {
                $bad++;
            }

            if ($this->option('bad') && !$needsUpgrade) {
                continue;
            }

            $rows[] = [
                $user->id,
                $user->email,
                $user->role,
                PasswordHash::describe($user->password),
                $needsUpgrade ? 'YES' : 'no',
            ];
        }

        if ($rows === []) {
            $this->warn('No matching users found.');

            return self::SUCCESS;
        }

        $this->table(['ID', 'Email', 'Role', 'Stored hash', 'Needs upgrade'], $rows);
        $this->newLine();
        $this->info("{$bad} account(s) are not using the current default hash configuration.");

        if (!defined('PASSWORD_ARGON2ID')) {
            $this->warn('This PHP build has no argon2 support: any argon2 account listed above CANNOT log in and must be reset with user:set-password.');
        }

        return self::SUCCESS;
    }
}
