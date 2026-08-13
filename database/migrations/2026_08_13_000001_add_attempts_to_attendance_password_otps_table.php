<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds brute-force protection to the attendance-checker password-reset OTP.
     * `attempts` counts consecutive wrong guesses against the current code and
     * `locked_until` hard-stops verification once that budget is exhausted,
     * mirroring the users-table OTP lockout used by the admin/employee 2FA flow.
     */
    public function up(): void
    {
        Schema::table('attendance_password_otps', function (Blueprint $table) {
            if (!Schema::hasColumn('attendance_password_otps', 'attempts')) {
                $table->unsignedTinyInteger('attempts')->default(0)->after('otp_hash');
            }
            if (!Schema::hasColumn('attendance_password_otps', 'locked_until')) {
                $table->timestamp('locked_until')->nullable()->after('expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendance_password_otps', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_password_otps', 'attempts')) {
                $table->dropColumn('attempts');
            }
            if (Schema::hasColumn('attendance_password_otps', 'locked_until')) {
                $table->dropColumn('locked_until');
            }
        });
    }
};
