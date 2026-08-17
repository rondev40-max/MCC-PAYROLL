package com.mcc.payroll.viewmodel

import android.app.Application
import androidx.lifecycle.AndroidViewModel
import androidx.lifecycle.viewModelScope
import com.mcc.payroll.MccApp
import com.mcc.payroll.data.remote.Announcement
import com.mcc.payroll.data.remote.Attendance
import com.mcc.payroll.data.remote.Employee
import com.mcc.payroll.data.remote.Payslip
import com.mcc.payroll.data.remote.Stats
import com.mcc.payroll.data.remote.User
import com.mcc.payroll.data.repo.Outcome
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.SharingStarted
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.flow.stateIn
import kotlinx.coroutines.launch

/** Whether a screen is loading, has content, or failed. */
data class ScreenState(
    val loading: Boolean = false,
    val refreshing: Boolean = false,
    val error: String? = null,
)

data class HomeData(
    val user: User? = null,
    val employee: Employee? = null,
    val stats: Stats = Stats(),
    val announcements: List<Announcement> = emptyList(),
    val payslips: List<Payslip> = emptyList(),
    val attendances: List<Attendance> = emptyList(),
)

data class AttendanceData(
    val attendances: List<Attendance> = emptyList(),
    val stats: Stats = Stats(),
)

sealed interface AuthEvent {
    /** The server rejected the stored token; the UI must return to sign-in. */
    data object SessionExpired : AuthEvent
}

class EmployeeViewModel(app: Application) : AndroidViewModel(app) {

    private val repo = (app as MccApp).repository
    private val session = (app as MccApp).session

    /** null while DataStore is still being read — distinct from "signed out". */
    val token: StateFlow<String?> = session.token
        .stateIn(viewModelScope, SharingStarted.Eagerly, null)

    /** True only once DataStore has actually answered, so the UI can hold the
     *  splash instead of flashing the login screen at an already-signed-in user. */
    val sessionResolved: StateFlow<Boolean> = session.token
        .map { true }
        .stateIn(viewModelScope, SharingStarted.Eagerly, false)

    val cachedName: StateFlow<String?> = session.name
        .stateIn(viewModelScope, SharingStarted.Eagerly, null)

    // ── Sign in ─────────────────────────────────────────────────────────────
    private val _loginState = MutableStateFlow(ScreenState())
    val loginState: StateFlow<ScreenState> = _loginState.asStateFlow()

    fun login(email: String, password: String, onSuccess: () -> Unit) {
        if (email.isBlank() || password.isBlank()) {
            _loginState.value = ScreenState(error = "Enter your email and password.")
            return
        }

        _loginState.value = ScreenState(loading = true)

        viewModelScope.launch {
            when (val result = repo.login(email, password)) {
                is Outcome.Ok -> {
                    _loginState.value = ScreenState()
                    loadHome()
                    onSuccess()
                }

                is Outcome.Failed -> _loginState.value = ScreenState(error = result.message)
            }
        }
    }

    fun dismissLoginError() {
        _loginState.value = _loginState.value.copy(error = null)
    }

    // ── Home ────────────────────────────────────────────────────────────────
    private val _homeState = MutableStateFlow(ScreenState(loading = true))
    val homeState: StateFlow<ScreenState> = _homeState.asStateFlow()

    private val _home = MutableStateFlow(HomeData())
    val home: StateFlow<HomeData> = _home.asStateFlow()

    fun loadHome(refresh: Boolean = false) {
        _homeState.value = if (refresh) {
            _homeState.value.copy(refreshing = true, error = null)
        } else {
            ScreenState(loading = true)
        }

        viewModelScope.launch {
            when (val result = repo.dashboard()) {
                is Outcome.Ok -> {
                    _home.value = HomeData(
                        user = result.data.user,
                        employee = result.data.employee,
                        stats = result.data.stats ?: Stats(),
                        announcements = result.data.announcements.orEmpty(),
                        payslips = result.data.payslips.orEmpty(),
                        attendances = result.data.attendances.orEmpty(),
                    )
                    _homeState.value = ScreenState()
                }

                is Outcome.Failed -> {
                    _homeState.value = ScreenState(error = result.message)
                    if (result.unauthorized) expireSession()
                }
            }
        }
    }

    // ── Attendance ──────────────────────────────────────────────────────────
    private val _attendanceState = MutableStateFlow(ScreenState(loading = true))
    val attendanceState: StateFlow<ScreenState> = _attendanceState.asStateFlow()

    private val _attendance = MutableStateFlow(AttendanceData())
    val attendance: StateFlow<AttendanceData> = _attendance.asStateFlow()

    fun loadAttendance(refresh: Boolean = false) {
        _attendanceState.value = if (refresh) {
            _attendanceState.value.copy(refreshing = true, error = null)
        } else {
            ScreenState(loading = true)
        }

        viewModelScope.launch {
            when (val result = repo.attendance()) {
                is Outcome.Ok -> {
                    _attendance.value = AttendanceData(
                        attendances = result.data.attendances.orEmpty(),
                        stats = result.data.stats ?: Stats(),
                    )
                    _attendanceState.value = ScreenState()
                }

                is Outcome.Failed -> {
                    _attendanceState.value = ScreenState(error = result.message)
                    if (result.unauthorized) expireSession()
                }
            }
        }
    }

    // ── Payslips ────────────────────────────────────────────────────────────
    private val _payslipsState = MutableStateFlow(ScreenState(loading = true))
    val payslipsState: StateFlow<ScreenState> = _payslipsState.asStateFlow()

    private val _payslips = MutableStateFlow<List<Payslip>>(emptyList())
    val payslips: StateFlow<List<Payslip>> = _payslips.asStateFlow()

    fun loadPayslips(refresh: Boolean = false) {
        _payslipsState.value = if (refresh) {
            _payslipsState.value.copy(refreshing = true, error = null)
        } else {
            ScreenState(loading = true)
        }

        viewModelScope.launch {
            when (val result = repo.payslips()) {
                is Outcome.Ok -> {
                    _payslips.value = result.data.payslips.orEmpty()
                    _payslipsState.value = ScreenState()
                }

                is Outcome.Failed -> {
                    _payslipsState.value = ScreenState(error = result.message)
                    if (result.unauthorized) expireSession()
                }
            }
        }
    }

    // ── Profile ─────────────────────────────────────────────────────────────
    private val _profileState = MutableStateFlow(ScreenState(loading = true))
    val profileState: StateFlow<ScreenState> = _profileState.asStateFlow()

    private val _profile = MutableStateFlow(HomeData())
    val profile: StateFlow<HomeData> = _profile.asStateFlow()

    fun loadProfile(refresh: Boolean = false) {
        _profileState.value = if (refresh) {
            _profileState.value.copy(refreshing = true, error = null)
        } else {
            ScreenState(loading = true)
        }

        viewModelScope.launch {
            when (val result = repo.profile()) {
                is Outcome.Ok -> {
                    _profile.value = HomeData(
                        user = result.data.user,
                        employee = result.data.employee,
                        stats = result.data.stats ?: Stats(),
                    )
                    _profileState.value = ScreenState()
                }

                is Outcome.Failed -> {
                    _profileState.value = ScreenState(error = result.message)
                    if (result.unauthorized) expireSession()
                }
            }
        }
    }

    // ── Session ─────────────────────────────────────────────────────────────
    private val _authEvents = MutableStateFlow<AuthEvent?>(null)
    val authEvents: StateFlow<AuthEvent?> = _authEvents.asStateFlow()

    fun consumeAuthEvent() {
        _authEvents.value = null
    }

    private fun expireSession() {
        viewModelScope.launch {
            repo.logout()
            _authEvents.value = AuthEvent.SessionExpired
        }
    }

    fun logout(onDone: () -> Unit) {
        viewModelScope.launch {
            repo.logout()
            _home.value = HomeData()
            _payslips.value = emptyList()
            _attendance.value = AttendanceData()
            _profile.value = HomeData()
            onDone()
        }
    }
}
