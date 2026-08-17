package com.mcc.payroll.data.repo

import com.google.gson.Gson
import com.mcc.payroll.data.local.SessionStore
import com.mcc.payroll.data.remote.ApiError
import com.mcc.payroll.data.remote.ApiService
import com.mcc.payroll.data.remote.AttendanceResponse
import com.mcc.payroll.data.remote.DashboardResponse
import com.mcc.payroll.data.remote.LoginRequest
import com.mcc.payroll.data.remote.PayslipsResponse
import com.mcc.payroll.data.remote.ProfileResponse
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import retrofit2.Response
import java.io.IOException

/**
 * Outcome of a call. Errors arrive as a message the UI can show verbatim, so no
 * screen has to translate HTTP codes into English itself.
 */
sealed interface Outcome<out T> {
    data class Ok<T>(val data: T) : Outcome<T>
    data class Failed(val message: String, val unauthorized: Boolean = false) : Outcome<Nothing>
}

class EmployeeRepository(
    private val api: ApiService,
    private val session: SessionStore,
) {

    suspend fun login(email: String, password: String): Outcome<Unit> = call {
        val response = api.login(LoginRequest(email.trim(), password))

        if (!response.isSuccessful) return@call failure(response)

        val token = response.body()?.token
            ?: return@call Outcome.Failed("The server did not return a session token.")

        session.save(token, response.body()?.user?.name, response.body()?.user?.email)
        Outcome.Ok(Unit)
    }

    suspend fun dashboard(): Outcome<DashboardResponse> = call { unwrap(api.dashboard()) }

    suspend fun attendance(): Outcome<AttendanceResponse> = call { unwrap(api.attendance()) }

    suspend fun payslips(): Outcome<PayslipsResponse> = call { unwrap(api.payslips()) }

    suspend fun profile(): Outcome<ProfileResponse> = call { unwrap(api.profile()) }

    suspend fun logout() = session.clear()

    // ── plumbing ────────────────────────────────────────────────────────────

    private suspend fun <T> call(block: suspend () -> Outcome<T>): Outcome<T> =
        withContext(Dispatchers.IO) {
            try {
                block()
            } catch (e: IOException) {
                // No connection, DNS failure, timeout — all things the employee
                // can act on, so say so plainly rather than showing a stack trace.
                Outcome.Failed("Can't reach the server. Check your connection and try again.")
            } catch (e: Exception) {
                Outcome.Failed("Something went wrong: ${e.message ?: "unknown error"}")
            }
        }

    private fun <T> unwrap(response: Response<T>): Outcome<T> {
        if (!response.isSuccessful) return failure(response)
        val body = response.body() ?: return Outcome.Failed("The server returned an empty response.")
        return Outcome.Ok(body)
    }

    /**
     * Turn a non-2xx into a readable message, preferring Laravel's own
     * `{"message": "..."}` when it sent one.
     */
    private fun failure(response: Response<*>): Outcome.Failed {
        val raw = try {
            response.errorBody()?.string()
        } catch (e: Exception) {
            null
        }

        val parsed = raw?.let {
            try {
                Gson().fromJson(it, ApiError::class.java)?.message
            } catch (e: Exception) {
                null
            }
        }

        val serverMessage: String? = parsed?.takeIf { it.isNotBlank() }

        val message: String = when {
            serverMessage != null -> serverMessage
            response.code() == 401 -> "Your email or password is incorrect."
            response.code() == 419 || response.code() == 403 -> "Your session expired. Please sign in again."
            response.code() >= 500 -> "The server had a problem. Please try again shortly."
            else -> "Request failed (HTTP ${response.code()})."
        }

        return Outcome.Failed(message, unauthorized = response.code() == 401)
    }
}
