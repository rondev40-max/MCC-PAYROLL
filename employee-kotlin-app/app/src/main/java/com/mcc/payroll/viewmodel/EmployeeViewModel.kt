package com.mcc.payroll.viewmodel

import android.app.Application
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.setValue
import androidx.lifecycle.AndroidViewModel
import androidx.lifecycle.viewModelScope
import com.mcc.payroll.api.DashboardResponse
import com.mcc.payroll.api.LoginRequest
import com.mcc.payroll.api.Payslip
import com.mcc.payroll.api.ProfileResponse
import com.mcc.payroll.api.RetrofitClient
import com.mcc.payroll.storage.TokenManager
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.launch
import retrofit2.Response

sealed interface UiState<out T> {
    object Idle : UiState<Nothing>
    object Loading : UiState<Nothing>
    data class Success<out T>(val data: T) : UiState<T>
    data class Error(val message: String) : UiState<Nothing>
}

class EmployeeViewModel(application: Application) : AndroidViewModel(application) {
    private val tokenManager = TokenManager(application)
    private val api = RetrofitClient.apiService

    // Authentication token flow
    private val _tokenFlow = MutableStateFlow<String?>(null)
    val tokenFlow: StateFlow<String?> = _tokenFlow.asStateFlow()

    // Screen States
    var loginState by mutableStateOf<UiState<String>>(UiState.Idle)
        private set

    var dashboardState by mutableStateOf<UiState<DashboardResponse>>(UiState.Idle)
        private set

    var payslipsState by mutableStateOf<UiState<List<Payslip>>>(UiState.Idle)
        private set

    var profileState by mutableStateOf<UiState<ProfileResponse>>(UiState.Idle)
        private set

    init {
        // Collect saved token on init
        viewModelScope.launch {
            _tokenFlow.value = tokenManager.token.first()
        }
    }

    private fun resolveErrorMessage(
        response: Response<*>?,
        fallback: String,
        throwable: Throwable? = null
    ): String {
        val bodyText = response?.errorBody()?.string().orEmpty().trim()
        val parsedMessage = bodyText.extractApiMessage()
        if (parsedMessage != null) {
            return parsedMessage
        }

        return when (response?.code()) {
            401 -> "Your session has expired. Please sign in again."
            403 -> "You don't have permission to access this information."
            404 -> "The requested information could not be found."
            in 500..599 -> "The server is currently unavailable. Please try again shortly."
            else -> {
                val message = throwable?.message.orEmpty().lowercase()
                when {
                    message.contains("timeout") || message.contains("timed out") ->
                        "The request timed out. Please check your connection and try again."
                    message.contains("unable to resolve host") || message.contains("unknownhost") ||
                        message.contains("connection refused") || message.contains("socket") ||
                        message.contains("network") || message.contains("ssl") ->
                        "We couldn't reach the server. Please check your connection and try again."
                    else -> fallback
                }
            }
        }
    }

    private fun String.extractApiMessage(): String? {
        val trimmed = trim()
        if (trimmed.isEmpty()) return null

        val patterns = listOf(
            Regex("\"message\"\\s*:\\s*\"([^\"]+)\""),
            Regex("\"error\"\\s*:\\s*\"([^\"]+)\""),
            Regex("\"detail\"\\s*:\\s*\"([^\"]+)\""),
            Regex("\"msg\"\\s*:\\s*\"([^\"]+)\"")
        )

        for (pattern in patterns) {
            val match = pattern.find(trimmed)
            val value = match?.groupValues?.getOrNull(1)?.trim()
            if (!value.isNullOrBlank()) {
                return value.replace("\\n", " ").replace("\\r", " ")
            }
        }

        return trimmed.takeIf {
            it.length < 300 && !it.contains("<") && !it.contains("html", ignoreCase = true)
        }
    }

    /**
     * Submit login request
     */
    fun login(email: String, javaPassword: String, onSuccess: () -> Unit) {
        viewModelScope.launch {
            loginState = UiState.Loading
            try {
                val response = api.login(LoginRequest(email, javaPassword))
                if (response.isSuccessful && response.body() != null) {
                    val body = response.body()!!
                    tokenManager.saveToken(body.token)
                    _tokenFlow.value = body.token
                    loginState = UiState.Success("Login successful!")
                    onSuccess()
                } else {
                    loginState = UiState.Error(
                        resolveErrorMessage(
                            response,
                            "Invalid email or password. Please try again."
                        )
                    )
                }
            } catch (e: Exception) {
                loginState = UiState.Error(
                    resolveErrorMessage(
                        null,
                        "We couldn't sign you in right now. Please try again.",
                        e
                    )
                )
            }
        }
    }

    /**
     * Load Dashboard Data
     */
    fun fetchDashboard() {
        viewModelScope.launch {
            dashboardState = UiState.Loading
            val rawToken = _tokenFlow.value ?: run {
                dashboardState = UiState.Error("Please sign in again to continue.")
                return@launch
            }
            val bearerToken = "Bearer $rawToken"
            try {
                val response = api.getDashboard(bearerToken)
                if (response.isSuccessful && response.body() != null) {
                    dashboardState = UiState.Success(response.body()!!)
                } else {
                    dashboardState = UiState.Error(
                        resolveErrorMessage(
                            response,
                            "We couldn't load your dashboard right now."
                        )
                    )
                }
            } catch (e: Exception) {
                dashboardState = UiState.Error(
                    resolveErrorMessage(
                        null,
                        "We couldn't load your dashboard right now.",
                        e
                    )
                )
            }
        }
    }

    /**
     * Load Payslip Records
     */
    fun fetchPayslips() {
        viewModelScope.launch {
            payslipsState = UiState.Loading
            val rawToken = _tokenFlow.value ?: run {
                payslipsState = UiState.Error("Please sign in again to continue.")
                return@launch
            }
            val bearerToken = "Bearer $rawToken"
            try {
                val response = api.getPayslips(bearerToken)
                if (response.isSuccessful && response.body() != null) {
                    payslipsState = UiState.Success(response.body()!!.payslips)
                } else {
                    payslipsState = UiState.Error(
                        resolveErrorMessage(
                            response,
                            "We couldn't load your payslips right now."
                        )
                    )
                }
            } catch (e: Exception) {
                payslipsState = UiState.Error(
                    resolveErrorMessage(
                        null,
                        "We couldn't load your payslips right now.",
                        e
                    )
                )
            }
        }
    }

    /**
     * Load Employee Profile Info
     */
    fun fetchProfile() {
        viewModelScope.launch {
            profileState = UiState.Loading
            val rawToken = _tokenFlow.value ?: run {
                profileState = UiState.Error("Please sign in again to continue.")
                return@launch
            }
            val bearerToken = "Bearer $rawToken"
            try {
                val response = api.getProfile(bearerToken)
                if (response.isSuccessful && response.body() != null) {
                    profileState = UiState.Success(response.body()!!)
                } else {
                    profileState = UiState.Error(
                        resolveErrorMessage(
                            response,
                            "We couldn't load your profile right now."
                        )
                    )
                }
            } catch (e: Exception) {
                profileState = UiState.Error(
                    resolveErrorMessage(
                        null,
                        "We couldn't load your profile right now.",
                        e
                    )
                )
            }
        }
    }

    /**
     * Sign out / Clear token
     */
    fun logout(onComplete: () -> Unit) {
        viewModelScope.launch {
            tokenManager.clearToken()
            _tokenFlow.value = null
            loginState = UiState.Idle
            onComplete()
        }
    }
}
