package com.mcc.payroll.api

import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.Header
import retrofit2.http.POST

// ─── Data Models ───

data class LoginRequest(
    val email: String,
    val password: String,
    val device_name: String = "Android Mobile"
)

data class LoginResponse(
    val token: String,
    val user: User
)

data class User(
    val id: Int,
    val name: String,
    val email: String,
    val role: String
)

data class Employee(
    val id: Int,
    val name: String?,
    val first_name: String?,
    val last_name: String?,
    val email: String?,
    val position: String?,
    val hourly_salary: Double?,
    val basic_salary: Double?,
    val department_id: Int?,
    val designation: String?,
    val department: String?
)

data class Stats(
    val present_days: Int = 0,
    val absent_days: Int = 0,
    val late_days: Int = 0,
    val total_hours: String = "0h",
    val today_time_in: String = "—",
    val today_time_out: String = "—",
    val today_hours: String = "0h"
)

data class DashboardResponse(
    val user: User,
    val employee: Employee?,
    val stats: Stats,
    val announcements: List<Announcement>,
    val payslips: List<Payslip>,
    val attendances: List<Attendance>
)

data class Attendance(
    val id: Int,
    val employee_id: Int,
    val date: String,
    val time_in: String?,
    val time_out: String?,
    val status: String,
    val hours_rendered: Double?
)

data class Payslip(
    val id: Int,
    val period: String?,
    val year: Int?,
    val month: String?,
    val net_pay: Double?,
    val sent_at: String?
)

data class PayslipsResponse(
    val payslips: List<Payslip>
)

data class Announcement(
    val id: Int,
    val title: String,
    val content: String,
    val created_at: String
)

data class ProfileResponse(
    val user: User,
    val employee: Employee?,
    val stats: Stats?
)

// ─── Retrofit Interface ───

interface ApiService {
    @POST("mobile/login")
    suspend fun login(
        @Body request: LoginRequest
    ): Response<LoginResponse>

    @GET("mobile/dashboard")
    suspend fun getDashboard(
        @Header("Authorization") token: String
    ): Response<DashboardResponse>

    @GET("mobile/payslips")
    suspend fun getPayslips(
        @Header("Authorization") token: String
    ): Response<PayslipsResponse>

    @GET("mobile/profile")
    suspend fun getProfile(
        @Header("Authorization") token: String
    ): Response<ProfileResponse>
}
