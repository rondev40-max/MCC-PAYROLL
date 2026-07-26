package com.mcc.payroll.viewmodel

import android.app.Application
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.setValue
import androidx.lifecycle.AndroidViewModel
import androidx.lifecycle.viewModelScope
import com.mcc.payroll.api.Announcement
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
                    loginState = UiState.Error(response.errorBody()?.string() ?: "Invalid login credentials.")
                }
            } catch (e: Exception) {
                loginState = UiState.Error(e.message ?: "Connection error. Please try again.")
            }
        }
    }

    /**
     * Load Dashboard Data
     */
    fun fetchDashboard() {
        viewModelScope.launch {
            dashboardState = UiState.Loading
            val rawToken = _tokenFlow.value ?: return@launch
            val bearerToken = "Bearer $rawToken"
            try {
                val response = api.getDashboard(bearerToken)
                if (response.isSuccessful && response.body() != null) {
                    dashboardState = UiState.Success(response.body()!!)
                } else {
                    dashboardState = UiState.Error(response.message())
                }
            } catch (e: Exception) {
                dashboardState = UiState.Error(e.message ?: "Failed to reach server.")
            }
        }
    }

    /**
     * Load Payslip Records
     */
    fun fetchPayslips() {
        viewModelScope.launch {
            payslipsState = UiState.Loading
            val rawToken = _tokenFlow.value ?: return@launch
            val bearerToken = "Bearer $rawToken"
            try {
                val response = api.getPayslips(bearerToken)
                if (response.isSuccessful && response.body() != null) {
                    payslipsState = UiState.Success(response.body()!!)
                } else {
                    payslipsState = UiState.Error(response.message())
                }
            } catch (e: Exception) {
                payslipsState = UiState.Error(e.message ?: "Failed to reach server.")
            }
        }
    }

    /**
     * Load Employee Profile Info
     */
    fun fetchProfile() {
        viewModelScope.launch {
            profileState = UiState.Loading
            val rawToken = _tokenFlow.value ?: return@launch
            val bearerToken = "Bearer $rawToken"
            try {
                val response = api.getProfile(bearerToken)
                if (response.isSuccessful && response.body() != null) {
                    profileState = UiState.Success(response.body()!!)
                } else {
                    profileState = UiState.Error(response.message())
                }
            } catch (e: Exception) {
                profileState = UiState.Error(e.message ?: "Failed to reach server.")
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
