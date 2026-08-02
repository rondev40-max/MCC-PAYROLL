package com.mcc.payroll

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Home
import androidx.compose.material.icons.filled.List
import androidx.compose.material.icons.filled.Person
import androidx.compose.material3.Icon
import androidx.compose.material3.NavigationBar
import androidx.compose.material3.NavigationBarItem
import androidx.compose.material3.NavigationBarItemDefaults
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavGraph.Companion.findStartDestination
import androidx.navigation.NavHostController
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.currentBackStackEntryAsState
import androidx.navigation.compose.rememberNavController
import com.mcc.payroll.ui.DashboardScreen
import com.mcc.payroll.ui.LoginScreen
import com.mcc.payroll.ui.PayslipsScreen
import com.mcc.payroll.ui.ProfileScreen
import com.mcc.payroll.viewmodel.EmployeeViewModel

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContent {
            MccPayrollApp()
        }
    }
}

sealed class Screen(val route: String, val title: String, val icon: @Composable () -> Unit) {
    object Login : Screen("login", "Login", {})
    object Dashboard : Screen("dashboard", "Home", { Icon(Icons.Default.Home, "Home") })
    object Payslips : Screen("payslips", "Payslips", { Icon(Icons.Default.List, "Payslips") })
    object Profile : Screen("profile", "Profile", { Icon(Icons.Default.Person, "Profile") })
}

@Composable
fun MccPayrollApp() {
    val navController = rememberNavController()
    val viewModel: EmployeeViewModel = viewModel()
    val token by viewModel.tokenFlow.collectAsState()

    // Determine the start destination dynamically based on whether token exists
    val startDestination = if (token != null) Screen.Dashboard.route else Screen.Login.route

    Surface(
        modifier = Modifier.fillMaxSize(),
        color = Color(0xFFF1F5F9) // var(--bg) slate equivalent
    ) {
        val navBackStackEntry by navController.currentBackStackEntryAsState()
        val currentRoute = navBackStackEntry?.destination?.route

        val showBottomBar = currentRoute != Screen.Login.route && currentRoute != null

        Scaffold(
            bottomBar = {
                if (showBottomBar) {
                    AppBottomNavigation(navController = navController, currentRoute = currentRoute)
                }
            }
        ) { paddingValues ->
            NavHost(
                navController = navController,
                startDestination = startDestination,
                modifier = Modifier.padding(paddingValues)
            ) {
                composable(Screen.Login.route) {
                    LoginScreen(
                        viewModel = viewModel,
                        onLoginSuccess = {
                            navController.navigate(Screen.Dashboard.route) {
                                popUpTo(Screen.Login.route) { inclusive = true }
                            }
                        }
                    )
                }
                composable(Screen.Dashboard.route) {
                    DashboardScreen(viewModel = viewModel)
                }
                composable(Screen.Payslips.route) {
                    PayslipsScreen(viewModel = viewModel)
                }
                composable(Screen.Profile.route) {
                    ProfileScreen(
                        viewModel = viewModel,
                        onLogout = {
                            viewModel.logout {
                                navController.navigate(Screen.Login.route) {
                                    popUpTo(Screen.Dashboard.route) { inclusive = true }
                                }
                            }
                        }
                    )
                }
            }
        }
    }
}

@Composable
fun AppBottomNavigation(navController: NavHostController, currentRoute: String?) {
    val items = listOf(
        Screen.Dashboard,
        Screen.Payslips,
        Screen.Profile
    )

    NavigationBar(
        modifier = Modifier.padding(horizontal = 16.dp, vertical = 8.dp),
        containerColor = Color.White,
        tonalElevation = 8.dp(),
        shape = RoundedCornerShape(24.dp)
    ) {
        items.forEach { screen ->
            NavigationBarItem(
                icon = screen.icon,
                label = { Text(screen.title, style = androidx.compose.ui.text.TextStyle(fontWeight = androidx.compose.ui.text.font.FontWeight.Bold)) },
                selected = currentRoute == screen.route,
                colors = NavigationBarItemDefaults.colors(
                    selectedIconColor = Color(0xFF2563EB),
                    selectedTextColor = Color(0xFF2563EB),
                    unselectedIconColor = Color(0xFF94A3B8),
                    unselectedTextColor = Color(0xFF94A3B8),
                    indicatorColor = Color(0xFFEFF6FF)
                ),
                onClick = {
                    navController.navigate(screen.route) {
                        popUpTo(navController.graph.findStartDestination().id) {
                            saveState = true
                        }
                        launchSingleTop = true
                        restoreState = true
                    }
                }
            )
        }
    }
}

// Simple helper to define DP values cleanly in Compose without depending on local extensions in plan
private fun Int.dp() = androidx.compose.ui.unit.dp
