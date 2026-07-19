<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\FulltimeTimesheet;
use App\Models\ParttimeTimesheet;
use App\Models\StaffTimesheet;
use App\Models\UtilityTimesheet;

class SyncEmployeeIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:sync-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync employee IDs in timesheet tables based on employee names';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting employee ID synchronization...');

        // Get all employees for name matching
        $employees = Employee::all()->keyBy('name');

        // Sync Fulltime Timesheets
        $this->syncTimesheetTable(FulltimeTimesheet::class, $employees, 'Fulltime');

        // Sync Parttime Timesheets
        $this->syncTimesheetTable(ParttimeTimesheet::class, $employees, 'Parttime');

        // Sync Staff Timesheets
        $this->syncTimesheetTable(StaffTimesheet::class, $employees, 'Staff');

        // Sync Utility Timesheets
        $this->syncTimesheetTable(UtilityTimesheet::class, $employees, 'Utility');

        $this->info('Employee ID synchronization completed!');
    }

    /**
     * Sync employee IDs for a specific timesheet table
     *
     * @param string $modelClass
     * @param \Illuminate\Support\Collection $employees
     * @param string $type
     */
    private function syncTimesheetTable($modelClass, $employees, $type)
    {
        $this->info("Syncing {$type} timesheets...");

        $timesheets = $modelClass::whereNull('employee_id')->get();
        $updated = 0;

        foreach ($timesheets as $timesheet) {
            if (isset($employees[$timesheet->employee_name])) {
                $timesheet->employee_id = $employees[$timesheet->employee_name]->id;
                $timesheet->save();
                $updated++;
            }
        }

        $this->info("Updated {$updated} {$type} timesheet records");
    }
}