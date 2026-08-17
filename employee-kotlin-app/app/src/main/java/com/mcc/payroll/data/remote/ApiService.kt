package com.mcc.payroll.data.remote

import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.POST

/**
 * Mirrors routes/api.php. Every route below /mobile except login sits behind
 * auth:sanctum — the bearer token is attached by AuthInterceptor rather than
 * being passed per-call, so a new endpoint cannot forget it.
 */
interface ApiService {

    @POST("mobile/login")
    suspend fun login(@Body request: LoginRequest): Response<LoginResponse>

    @GET("mobile/dashboard")
    suspend fun dashboard(): Response<DashboardResponse>

    @GET("mobile/attendance")
    suspend fun attendance(): Response<AttendanceResponse>

    @GET("mobile/payslips")
    suspend fun payslips(): Response<PayslipsResponse>

    @GET("mobile/announcements")
    suspend fun announcements(): Response<AnnouncementsResponse>

    @GET("mobile/profile")
    suspend fun profile(): Response<ProfileResponse>
}
