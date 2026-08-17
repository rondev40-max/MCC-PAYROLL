<?php

namespace App\Support;

use App\Models\Attendance;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Real service checks for the landing page's status board.
 *
 * The board used to be three hardcoded rows that read "Operational" no matter
 * what — so the first time the portal actually broke, the front page would have
 * calmly told every employee it was fine. A status indicator that cannot report
 * a problem is worse than no indicator, because people trust it.
 *
 * Each check below is cheap (one query at most) and degrades to "unknown"
 * rather than throwing, since nothing here is worth 500-ing the home page over.
 */
final class SystemStatus
{
    public const OK      = 'operational';
    public const DEGRADED = 'degraded';
    public const DOWN    = 'down';
    public const UNKNOWN = 'unknown';

    /**
     * @return list<array{key:string, label:string, state:string, detail:string}>
     */
    public static function all(): array
    {
        return [
            self::employeePortal(),
            self::attendanceTerminal(),
            self::mobileApp(),
        ];
    }

    /**
     * The portal is backed by the database — if that answers, the portal serves.
     */
    private static function employeePortal(): array
    {
        try {
            DB::connection()->getPdo();

            return self::row('portal', 'Employee Portal', self::OK, 'Signed-in services responding');
        } catch (Throwable $e) {
            return self::row('portal', 'Employee Portal', self::DOWN, 'Database unreachable');
        }
    }

    /**
     * Terminals are healthy when something has actually been logged recently.
     * Silence is the failure mode worth surfacing here: a terminal that stopped
     * writing looks identical to a quiet day until payroll is short.
     */
    private static function attendanceTerminal(): array
    {
        try {
            if (!Schema::hasTable('attendances')) {
                return self::row('attendance', 'Attendance Terminal', self::UNKNOWN, 'No attendance records yet');
            }

            $latest = Attendance::max('created_at') ?? Attendance::max('date');

            if (!$latest) {
                return self::row('attendance', 'Attendance Terminal', self::UNKNOWN, 'No check-ins recorded yet');
            }

            $when = Carbon::parse($latest);
            $ago  = $when->diffForHumans();

            // Two full days of silence spans a weekend without alarming anyone;
            // beyond that, a terminal is likely not reporting.
            if ($when->lessThan(Carbon::now()->subDays(2))) {
                return self::row('attendance', 'Attendance Terminal', self::DEGRADED, "No check-ins since {$ago}");
            }

            return self::row('attendance', 'Attendance Terminal', self::OK, "Last check-in {$ago}");
        } catch (Throwable $e) {
            return self::row('attendance', 'Attendance Terminal', self::UNKNOWN, 'Status unavailable');
        }
    }

    /**
     * The download only works if the APK is actually on disk.
     */
    private static function mobileApp(): array
    {
        try {
            $path = public_path('downloads/mcc-employee-app.apk');

            if (!is_file($path)) {
                return self::row('mobile', 'Mobile App', self::DOWN, 'Installer not available');
            }

            $size = filesize($path);

            // A few hundred bytes is a placeholder, not a build. Saying
            // "operational" about it would send people to a broken download.
            if ($size < 1024 * 512) {
                return self::row('mobile', 'Mobile App', self::DEGRADED, 'Placeholder build published');
            }

            $built = Carbon::createFromTimestamp(filemtime($path))->diffForHumans();

            return self::row('mobile', 'Mobile App', self::OK, "Build published {$built}");
        } catch (Throwable $e) {
            return self::row('mobile', 'Mobile App', self::UNKNOWN, 'Status unavailable');
        }
    }

    private static function row(string $key, string $label, string $state, string $detail): array
    {
        return compact('key', 'label', 'state', 'detail');
    }

    /**
     * One-word summary for the whole board.
     */
    public static function overall(array $rows): string
    {
        $states = array_column($rows, 'state');

        if (in_array(self::DOWN, $states, true))     return self::DOWN;
        if (in_array(self::DEGRADED, $states, true)) return self::DEGRADED;
        if (in_array(self::UNKNOWN, $states, true))  return self::UNKNOWN;

        return self::OK;
    }
}
