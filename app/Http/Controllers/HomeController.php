<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Support\SystemStatus;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * The public landing page.
 *
 * This used to be a closure in routes/web.php returning view('index') with no
 * data at all — which meant the announcement banner, guarded by
 * `@if(isset($announcement))`, could never render. The section had been dead
 * since it was written.
 */
class HomeController extends Controller
{
    public function index()
    {
        $statuses = SystemStatus::all();

        return view('index', [
            'announcement'  => $this->latestAnnouncement(),
            'statuses'      => $statuses,
            'overallStatus' => SystemStatus::overall($statuses),
        ]);
    }

    /**
     * Newest announcement, or null. Never allowed to break the home page.
     */
    private function latestAnnouncement(): ?Announcement
    {
        try {
            if (!Schema::hasTable('announcements')) {
                return null;
            }

            return Announcement::latest('created_at')->first();
        } catch (Throwable $e) {
            return null;
        }
    }
}
