package com.mcc.payroll

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.CalendarMonth
import androidx.compose.material.icons.outlined.GridView
import androidx.compose.material.icons.outlined.PersonOutline
import androidx.compose.material.icons.outlined.ReceiptLong
import androidx.compose.material3.CircularProgressIndicator
// Divider, not HorizontalDivider: compose-bom 2024.01.00 pins material3 1.1.2,
// and HorizontalDivider only arrives in 1.2.0.
import androidx.compose.material3.Divider
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.NavigationBar
import androidx.compose.material3.NavigationBarItem
import androidx.compose.material3.NavigationBarItemDefaults
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavGraph.Companion.findStartDestination
import androidx.navigation.NavHostController
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.currentBackStackEntryAsState
import androidx.navigation.compose.rememberNavController
import com.mcc.payroll.ui.AttendanceScreen
import com.mcc.payroll.ui.DashboardScreen
import com.mcc.payroll.ui.LoginScreen
import com.mcc.payroll.ui.PayslipsScreen
import com.mcc.payroll.ui.ProfileScreen
import com.mcc.payroll.ui.theme.MccPayrollTheme
import com.mcc.payroll.viewmodel.AuthEvent
import com.mcc.payroll.viewmodel.EmployeeViewModel

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContent {
            MccPayrollTheme {
                Surface(
                    modifier = Modifier.fillMaxSize(),
                    color = MaterialTheme.colorScheme.background,
                ) {
                    MccPayrollApp()
                }
            }
        }
    }
}

private sealed class Destination(
    val route: String,
    val label: String,
    val icon: ImageVector,
) {
    data object Home : Destination("home", "Home", Icons.Outlined.GridView)
    data object Attendance : Destination("attendance", "Attendance", Icons.Outlined.CalendarMonth)
    data object Payslips : Destination("payslips", "Payslips", Icons.Outlined.ReceiptLong)
    data object Profile : Destination("profile", "Profile", Icons.Outlined.PersonOutline)
}

private const val LOGIN_ROUTE = "login"

private val bottomTabs = listOf(
    Destination.Home,
    Destination.Attendance,
    Destination.Payslips,
    Destination.Profile,
)

@Composable
fun MccPayrollApp() {
    val navController = rememberNavController()
    val viewModel: EmployeeViewModel = viewModel()

    val token by viewModel.token.collectAsState()
    val sessionResolved by viewModel.sessionResolved.collectAsState()
    val authEvent by viewModel.authEvents.collectAsState()

    // Hold a spinner until DataStore answers. Deciding the start destination on
    // the initial null would flash the login screen at an already-signed-in user
    // and then yank it away — the classic Compose-plus-DataStore stutter.
    if (!sessionResolved) {
        Box(Modifier.fillMaxSize(), contentAlignment = Alignment.Center) {
            CircularProgressIndicator(strokeWidth = 2.5.dp)
        }
        return
    }

    // A rejected token anywhere in the app returns everyone to sign-in.
    LaunchedEffect(authEvent) {
        if (authEvent is AuthEvent.SessionExpired) {
            navController.navigate(LOGIN_ROUTE) { popUpTo(0) { inclusive = true } }
            viewModel.consumeAuthEvent()
        }
    }

    val backStackEntry by navController.currentBackStackEntryAsState()
    val currentRoute = backStackEntry?.destination?.route
    val showBottomBar = currentRoute != null && currentRoute != LOGIN_ROUTE

    Scaffold(
        containerColor = MaterialTheme.colorScheme.background,
        bottomBar = {
            if (showBottomBar) {
                Column {
                    Divider(color = MaterialTheme.colorScheme.outline)
                    BottomBar(navController, currentRoute)
                }
            }
        },
    ) { padding ->
        NavHost(
            navController = navController,
            startDestination = if (token.isNullOrBlank()) LOGIN_ROUTE else Destination.Home.route,
            modifier = Modifier.padding(padding),
        ) {
            composable(LOGIN_ROUTE) {
                LoginScreen(
                    viewModel = viewModel,
                    onLoginSuccess = {
                        navController.navigate(Destination.Home.route) {
                            popUpTo(LOGIN_ROUTE) { inclusive = true }
                        }
                    },
                )
            }
            composable(Destination.Home.route) { DashboardScreen(viewModel) }
            composable(Destination.Attendance.route) { AttendanceScreen(viewModel) }
            composable(Destination.Payslips.route) { PayslipsScreen(viewModel) }
            composable(Destination.Profile.route) {
                ProfileScreen(
                    viewModel = viewModel,
                    onLogout = {
                        viewModel.logout {
                            navController.navigate(LOGIN_ROUTE) {
                                // popUpTo(0) clears the whole back stack, so Back
                                // from the login screen cannot re-enter the app.
                                popUpTo(0) { inclusive = true }
                            }
                        }
                    },
                )
            }
        }
    }
}

@Composable
private fun BottomBar(navController: NavHostController, currentRoute: String?) {
    NavigationBar(
        containerColor = MaterialTheme.colorScheme.surface,
        tonalElevation = 0.dp,
    ) {
        bottomTabs.forEach { tab ->
            val selected = currentRoute == tab.route

            NavigationBarItem(
                selected = selected,
                onClick = {
                    if (!selected) {
                        navController.navigate(tab.route) {
                            // saveState/restoreState keep each tab's scroll
                            // position and loaded data across switches.
                            popUpTo(navController.graph.findStartDestination().id) {
                                saveState = true
                            }
                            launchSingleTop = true
                            restoreState = true
                        }
                    }
                },
                icon = { Icon(tab.icon, contentDescription = tab.label) },
                label = { Text(tab.label, style = MaterialTheme.typography.labelMedium) },
                colors = NavigationBarItemDefaults.colors(
                    selectedIconColor = MaterialTheme.colorScheme.primary,
                    selectedTextColor = MaterialTheme.colorScheme.primary,
                    indicatorColor = MaterialTheme.colorScheme.primaryContainer,
                    unselectedIconColor = MaterialTheme.colorScheme.onSurfaceVariant,
                    unselectedTextColor = MaterialTheme.colorScheme.onSurfaceVariant,
                ),
            )
        }
    }
}
