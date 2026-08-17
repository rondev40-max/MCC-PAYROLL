package com.mcc.payroll.data.remote

import com.google.gson.annotations.SerializedName

/**
 * Wire models for the mobile API endpoints under `api/mobile`.
 *
 * (Written without a glob: Kotlin nests block comments, so a literal slash-star
 * inside this KDoc would open a nested comment and swallow the rest of the file.)
 *
 * Field names mirror the JSON exactly, because these Eloquent models are
 * serialised whole by MobilePortalController — there is no API resource layer
 * renaming anything. Two of them are easy to get wrong:
 *
 *  - PayslipHistory has `pay_period` and `total_honorarium`, NOT `period`/`net_pay`.
 *  - Announcement has `message`, NOT `content`.
 *
 * Every field the server can omit or null is nullable here. Gson leaves absent
 * fields null regardless of Kotlin's declared type, so a non-null String that
 * the API skips would blow up later with a confusing NPE far from the cause.
 */

data class LoginRequest(
    val email: String,
    val password: String,
    @SerializedName("device_name") val deviceName: String = "Android",
)

data class LoginResponse(
    val message: String?,
    val token: String?,
    val user: User?,
)

data class User(
    val id: Int?,
    val name: String?,
    val email: String?,
    val role: String?,
)

data class Employee(
    val id: Int?,
    val name: String?,
    val email: String?,
    val position: String?,
    @SerializedName("hourly_salary") val hourlySalary: Double?,
    @SerializedName("department_id") val departmentId: Int?,
)

data class Stats(
    @SerializedName("present_days") val presentDays: Int = 0,
    @SerializedName("absent_days") val absentDays: Int = 0,
    @SerializedName("late_days") val lateDays: Int = 0,
    @SerializedName("total_hours") val totalHours: String = "0h",
    @SerializedName("today_time_in") val todayTimeIn: String = "—",
    @SerializedName("today_time_out") val todayTimeOut: String = "—",
    @SerializedName("today_hours") val todayHours: String = "0h",
)

data class Attendance(
    val id: Int?,
    @SerializedName("employee_id") val employeeId: Int?,
    val date: String?,
    @SerializedName("time_in") val timeIn: String?,
    @SerializedName("time_out") val timeOut: String?,
    val status: String?,
    @SerializedName("hours_rendered") val hoursRendered: Double?,
    val remarks: String?,
)

data class Payslip(
    val id: Int?,
    val name: String?,
    val email: String?,
    @SerializedName("employee_type") val employeeType: String?,
    val designation: String?,
    @SerializedName("pay_period") val payPeriod: String?,
    @SerializedName("total_honorarium") val totalHonorarium: Double?,
    @SerializedName("total_hours_or_days") val totalHoursOrDays: Double?,
    val rate: Double?,
    @SerializedName("sent_at") val sentAt: String?,
)

data class Announcement(
    val id: Int?,
    val title: String?,
    val message: String?,
    val type: String?,
    @SerializedName("created_at") val createdAt: String?,
)

data class DashboardResponse(
    val user: User?,
    val employee: Employee?,
    val stats: Stats?,
    val announcements: List<Announcement>?,
    val payslips: List<Payslip>?,
    val attendances: List<Attendance>?,
)

data class AttendanceResponse(
    val attendances: List<Attendance>?,
    val stats: Stats?,
)

data class PayslipsResponse(val payslips: List<Payslip>?)

data class AnnouncementsResponse(val announcements: List<Announcement>?)

data class ProfileResponse(
    val user: User?,
    val employee: Employee?,
    val stats: Stats?,
)

/** Laravel's shape for validation and auth failures. */
data class ApiError(val message: String?)
