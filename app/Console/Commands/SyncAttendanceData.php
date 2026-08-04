<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncAttendanceData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:sync {--force : Force sync without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync attendance data from checker portal to employee portal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('This will sync attendance data from timesheet records. Continue?')) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        try {
            $this->info('Starting attendance sync...');
            $syncedCount = 0;

            // Get all fulltime employees
            $fulltimeEmployees = DB::table('fulltime_timesheets')
                ->select('id', 'employee_id', 'employee_name', 'department', 'employee_type', 'days')
                ->get();

            $this->info("Found {$fulltimeEmployees->count()} fulltime employees");

            foreach ($fulltimeEmployees as $employee) {
                $syncedCount += $this->processEmployeeAttendance($employee, 'fulltime');
            }

            // Get all parttime employees
            $parttimeEmployees = DB::table('parttime_timesheets')
                ->select('id', 'employee_id', 'employee_name', 'department', 'employee_type', 'days')
                ->get();

            $this->info("Found {$parttimeEmployees->count()} parttime employees");

            foreach ($parttimeEmployees as $employee) {
                $syncedCount += $this->processEmployeeAttendance($employee, 'parttime');
            }

            $this->info("✓ Attendance sync completed successfully!");
            $this->line("Total records synced: <info>{$syncedCount}</info>");

            Log::info('Attendance sync completed', ['synced_records' => $syncedCount]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error syncing attendance: ' . $e->getMessage());
            Log::error('Attendance sync failed', ['error' => $e->getMessage()]);
            return Command::FAILURE;
        }
    }

    /**
     * Process individual employee attendance data
     */
    private function processEmployeeAttendance($employee, $type)
    {
        try {
            $syncedCount = 0;
            $days = json_decode($employee->days, true) ?? [];
            $dayNames = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            $today = now();
            $weekStart = $today->copy()->startOfWeek();

            foreach ($dayNames as $index => $day) {
                $dayDate = $weekStart->copy()->addDays($index);
                $hoursWorked = $days[$day] ?? 0;
                $status = 'absent';

                if ($hoursWorked > 0) {
                    $status = $hoursWorked >= 8 ? 'present' : 'late';
                }

                // Check if record exists
                $existingRecord = DB::table('attendances')
                    ->where('employee_id', $employee->employee_id)
                    ->where('date', $dayDate->toDateString())
                    ->first();

                if ($existingRecord) {
                    // Update existing record
                    DB::table('attendances')
                        ->where('id', $existingRecord->id)
                        ->update([
                            'hours_rendered' => $hoursWorked,
                            'status' => $status,
                            'course' => strtoupper($employee->department),
                            'employee_name' => $employee->employee_name,
                            'employee_type' => $type,
                            'updated_at' => now()
                        ]);
                } else {
                    // Create new record
                    DB::table('attendances')->insert([
                        'employee_id' => $employee->employee_id,
                        'date' => $dayDate->toDateString(),
                        'time_in' => '08:00:00',
                        'time_out' => '17:00:00',
                        'hours_rendered' => $hoursWorked,
                        'status' => $status,
                        'course' => strtoupper($employee->department),
                        'employee_name' => $employee->employee_name,
                        'employee_type' => $type,
                        'remarks' => 'Synced from attendance checker',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                $syncedCount++;
            }

            return $syncedCount;

        } catch (\Exception $e) {
            $this->error("Error processing employee {$employee->employee_name}: " . $e->getMessage());
            Log::error('Error processing employee attendance', [
                'employee_id' => $employee->employee_id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
}
