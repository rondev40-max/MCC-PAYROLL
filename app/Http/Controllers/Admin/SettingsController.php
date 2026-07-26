<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsController extends Controller
{
    /**
     * Display a listing of the settings.
     */
    public function index()
    {
        // Load all settings grouped by group name
        $settings = Setting::all()->groupBy('group');

        return view('admin.settings', compact('settings'));
    }

    /**
     * Update the settings in bulk.
     */
    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        // Iterate and update each setting key
        foreach ($request->settings as $key => $value) {
            // Determine group based on the setting key
            $group = 'general';
            if (in_array($key, ['school_name', 'school_address', 'signatory_name', 'signatory_title'])) {
                $group = 'profile';
            } elseif (in_array($key, ['grace_period_minutes', 'overtime_threshold_hours', 'restrict_by_ip'])) {
                $group = 'attendance';
            } elseif (in_array($key, ['enable_login_otp', 'bcc_admin_on_payslips'])) {
                $group = 'security';
            } elseif (in_array($key, ['currency_symbol', 'default_cutoff_period'])) {
                $group = 'payroll';
            }

            Setting::set($key, $value, $group);
        }

        // Handle checkboxes/boolean toggles that are missing if unchecked
        $checkboxes = ['restrict_by_ip', 'enable_login_otp', 'bcc_admin_on_payslips'];
        foreach ($checkboxes as $checkbox) {
            if (!isset($request->settings[$checkbox])) {
                // If it is missing from the payload, it was unchecked. Save as '0'
                $group = in_array($checkbox, ['restrict_by_ip']) ? 'attendance' : 'security';
                Setting::set($checkbox, '0', $group);
            }
        }

        return redirect()->route('admin.settings.index')->with('success', 'Settings updated successfully!');
    }
}
