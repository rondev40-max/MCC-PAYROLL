<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Kunin ang lahat ng logs, i-join sa users table para makuha ang pangalan,
        // at i-order base sa pinakabagong event.
        $activityLogs = DB::table('activity_logs')
            ->join('users', 'activity_logs.user_id', '=', 'users.id')
            ->select('users.name', 'users.email', 'users.role', 'activity_logs.*')
            ->orderBy('activity_logs.created_at', 'desc')
            ->paginate(25); // Mag-paginate para hindi bumagal kung marami na ang logs

        return view('admin.activity-log', compact('activityLogs'));
    }
}