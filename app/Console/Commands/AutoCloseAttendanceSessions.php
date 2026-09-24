<?php

namespace App\Console\Commands;

use App\Models\AttendanceSession;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AutoCloseAttendanceSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:auto-close {--hours= : Threshold in hours before closing open sessions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically close forgotten open attendance sessions older than threshold and mark for review.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hoursThreshold = (float) ($this->option('hours') ?: config('attendance.auto_close_after_hours', 14));
        $creditHours    = (float) config('attendance.auto_close_credit_hours', 8);
        $creditSeconds  = (int) ($creditHours * 3600);
        $cutoffTime     = now()->subHours($hoursThreshold);
        $today          = now()->toDateString();

        $openSessions = AttendanceSession::where('status', 'open')
            ->where(function ($q) use ($cutoffTime, $today) {
                $q->where('clocked_in_at', '<=', $cutoffTime)
                  ->orWhere('work_date', '<', $today);
            })
            ->with('employee')
            ->get();

        if ($openSessions->isEmpty()) {
            $this->info('No forgotten open attendance sessions found.');
            return self::SUCCESS;
        }

        $closedCount = 0;

        foreach ($openSessions as $session) {
            DB::transaction(function () use ($session, $creditHours, $creditSeconds, &$closedCount) {
                $locked = AttendanceSession::whereKey($session->id)
                    ->where('status', 'open')
                    ->lockForUpdate()
                    ->first();

                if (! $locked) {
                    return;
                }

                $clockedOut = $locked->clocked_in_at->copy()->addHours($creditHours);

                $locked->update([
                    'clocked_out_at' => $clockedOut,
                    'worked_seconds' => $creditSeconds,
                    'status'         => 'needs_review',
                    'auto_closed'    => true,
                    'review_note'    => 'Auto-closed by system: employee did not clock out within allowed window.',
                ]);

                $closedCount++;
            });
        }

        $this->info("Successfully auto-closed {$closedCount} forgotten open attendance session(s).");

        return self::SUCCESS;
    }
}
