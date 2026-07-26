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
    val role: String,
    val course: String? = null
)

data class DashboardResponse(
    val welcome_message: String,
    val employee_name: String,
    val department: String?,
    val position: String?,
    val monthly_salary: Double?,
    val recent_payslips: List<Payslip>,
    val announcements: List<Announcement>
)

data class Payslip(
    val id: Int,
    val period: String,
    val year: Int,
    val month: String,
    val net_pay: Double,
    val sent_at: String
)

data class Announcement(
    val id: Int,
    val title: String,
    val content: String,
    val created_at: String
)

data class ProfileResponse(
    val user: User,
    val designation: String?,
    val department: String?,
    val daily_rate: Double?,
    val last_seen: String?
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
    ): Response<List<Payslip>>

    @GET("mobile/profile")
    suspend fun getProfile(
        @Header("Authorization") token: String
    ): Response<ProfileResponse>
}
