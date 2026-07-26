<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. School Profile Settings
        Setting::set('school_name', 'Madridejos Community College', 'profile');
        Setting::set('school_address', 'Poblacion, Madridejos, Cebu, Philippines', 'profile');
        Setting::set('signatory_name', 'Dr. Jorex Sarraga', 'profile');
        Setting::set('signatory_title', 'College President', 'profile');

        // 2. Attendance & Timesheet Policies
        Setting::set('grace_period_minutes', '15', 'attendance');
        Setting::set('overtime_threshold_hours', '8', 'attendance');
        Setting::set('restrict_by_ip', '0', 'attendance'); // 0 = disabled, 1 = enabled

        // 3. Security & Mail Settings
        Setting::set('enable_login_otp', '0', 'security'); // 0 = disabled, 1 = enabled
        Setting::set('bcc_admin_on_payslips', '0', 'security'); // 0 = disabled, 1 = enabled

        // 4. Payroll Defaults
        Setting::set('currency_symbol', '₱', 'payroll');
        Setting::set('default_cutoff_period', '1-15', 'payroll');
    }
}
