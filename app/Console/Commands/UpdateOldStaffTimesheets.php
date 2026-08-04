<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StaffTimesheet;
use Carbon\Carbon;

class UpdateOldStaffTimesheets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timesheets:update-old-staff';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates old staff timesheets by setting missing period, month, and year values based on their creation or date field.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting update of old staff timesheet records...');

        // Hanapin ang mga records na walang month, year, o period (ang lumang data)
        $oldTimesheets = StaffTimesheet::whereNull('month')
                                    ->orWhereNull('year')
                                    ->orWhereNull('period')
                                    ->get();

        $count = 0;
        foreach ($oldTimesheets as $timesheet) {
            // Gamitin ang 'date' column kung available, kung hindi, 'created_at'
            $date = $timesheet->date ? Carbon::parse($timesheet->date) : Carbon::parse($timesheet->created_at);

            $timesheet->month = $date->month;
            $timesheet->year = $date->year;
            $timesheet->period = ($date->day <= 15) ? '1-15' : '16-end';
            
            // I-save ang pagbabago
            $timesheet->save();
            $count++;
        }

        $this->info("Successfully updated {$count} records in staff_timesheets table!");
        return Command::SUCCESS;
    }
}