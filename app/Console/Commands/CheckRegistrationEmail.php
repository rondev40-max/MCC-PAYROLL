<?php

namespace App\Console\Commands;

use App\Models\AdminPersonnelTimesheet;
use App\Models\Employee;
use App\Models\FulltimeTimesheet;
use App\Models\ParttimeTimesheet;
use App\Models\StaffTimesheet;
use App\Models\User;
use App\Models\WatchmanTimesheet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Explains why registering with an email is (or is not) accepted:
 *   php artisan registration:check someone@gmail.com
 */
class CheckRegistrationEmail extends Command
{
    protected $signature = 'registration:check {email : The email address to look up}';

    protected $description = 'Show where an email is found on the roster and whether it already has an account';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        $this->line("Looking up: {$email}");

        $employee = Employee::whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();
        $this->line($employee ? "employees table: FOUND (id {$employee->id}, {$employee->name})" : 'employees table: not found');

        $sources = [
            'fulltime_timesheets' => FulltimeTimesheet::class,
            'parttime_timesheets' => ParttimeTimesheet::class,
            'staff_timesheets' => StaffTimesheet::class,
            'admin_personnel_timesheets' => AdminPersonnelTimesheet::class,
            'watchman_timesheets' => WatchmanTimesheet::class,
        ];

        $onRoster = (bool) $employee;
        foreach ($sources as $table => $model) {
            if (!Schema::hasColumn($table, 'email')) {
                $this->line("{$table}: no email column");
                continue;
            }

            $count = $model::whereRaw('LOWER(TRIM(email)) = ?', [$email])->count();
            $this->line("{$table}: " . ($count ? "FOUND ({$count} row(s))" : 'not found'));
            $onRoster = $onRoster || $count > 0;
        }

        $user = User::whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();
        if ($user) {
            $this->line("users table: ACCOUNT EXISTS (id {$user->id}, role {$user->role}, status {$user->status}, email verified: " . ($user->email_verified_at ? 'yes' : 'no') . ')');
        } else {
            $this->line('users table: no account yet');
        }

        $this->newLine();
        if ($user) {
            $this->warn('Registration will say "could not create": this email already has an account. Sign in instead.');
        } elseif (!$onRoster) {
            $this->warn('Registration will say "could not create": this email is not on the roster. Check the spelling in the master list.');
        } else {
            $this->info('Registration should be accepted for this email.');
        }

        return self::SUCCESS;
    }
}
