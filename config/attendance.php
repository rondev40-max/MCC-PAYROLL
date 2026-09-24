<?php

return [
    // Standard work hours per day (exclusive of lunch break).
    'hours_per_day' => 8,

    // Sessions running longer than this (hours) are flagged for administrator review.
    'max_session_hours' => 18,

    // Automatically close forgotten open sessions running longer than this (hours).
    'auto_close_after_hours' => 14,

    // Default duty hours to temporarily credit when a session is auto-closed pending review.
    'auto_close_credit_hours' => 8,

    // Grace period in minutes before arrival is considered late/tardy.
    'late_grace_minutes' => 15,

    // Comma-separated list of allowed campus IPs/CIDR subnets (empty string = unrestricted).
    'campus_ip_whitelist' => env('ATTENDANCE_CAMPUS_IPS', ''),

    // Record source IP and browser user agent for each punch.
    'track_client_ip' => true,
];
