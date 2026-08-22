package com.mcc.payroll.ui

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.AlternateEmail
import androidx.compose.material.icons.outlined.Badge
import androidx.compose.material.icons.outlined.Logout
import androidx.compose.material.icons.outlined.WorkOutline
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import com.mcc.payroll.BuildConfig
import com.mcc.payroll.ui.components.ErrorState
import com.mcc.payroll.ui.components.LoadingState
import com.mcc.payroll.ui.components.Refreshable
import com.mcc.payroll.ui.components.MccCard
import com.mcc.payroll.ui.components.SectionLabel
import com.mcc.payroll.ui.theme.mutedColor
import com.mcc.payroll.viewmodel.EmployeeViewModel

@Composable
fun ProfileScreen(
    viewModel: EmployeeViewModel,
    onLogout: () -> Unit,
) {
    val state by viewModel.profileState.collectAsState()
    val data by viewModel.profile.collectAsState()
    var confirmSignOut by remember { mutableStateOf(false) }

    LaunchedEffect(Unit) { if (data.user == null) viewModel.loadProfile() }

    if (confirmSignOut) {
        AlertDialog(
            onDismissRequest = { confirmSignOut = false },
            title = { Text("Sign out?") },
            text = { Text("You'll need your email and password to sign back in.") },
            confirmButton = {
                TextButton(onClick = {
                    confirmSignOut = false
                    onLogout()
                }) { Text("Sign out") }
            },
            dismissButton = {
                TextButton(onClick = { confirmSignOut = false }) { Text("Cancel") }
            },
            shape = MaterialTheme.shapes.medium,
        )
    }

    when {
        state.loading -> LoadingState()
        state.error != null -> ErrorState(state.error!!, onRetry = { viewModel.loadProfile() })
        else -> Refreshable(
            refreshing = state.refreshing,
            onRefresh = { viewModel.loadProfile(refresh = true) },
        ) {
            LazyColumn(
                modifier = Modifier.fillMaxSize(),
                contentPadding = PaddingValues(start = 20.dp, end = 20.dp, top = 32.dp, bottom = 32.dp),
                verticalArrangement = Arrangement.spacedBy(14.dp),
            ) {
                item {
                    Column(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalAlignment = Alignment.CenterHorizontally,
                    ) {
                        Box(
                            modifier = Modifier
                                .size(76.dp)
                                .background(
                                    MaterialTheme.colorScheme.primary,
                                    RoundedCornerShape(24.dp),
                                ),
                            contentAlignment = Alignment.Center,
                        ) {
                            Text(
                                text = Format.initials(data.user?.name),
                                style = MaterialTheme.typography.headlineSmall,
                                color = MaterialTheme.colorScheme.onPrimary,
                            )
                        }
                        Text(
                            text = data.user?.name.orEmpty(),
                            style = MaterialTheme.typography.headlineSmall,
                            color = MaterialTheme.colorScheme.onBackground,
                            modifier = Modifier.padding(top = 14.dp),
                        )
                        Text(
                            text = data.employee?.position ?: data.user?.role.orEmpty(),
                            style = MaterialTheme.typography.bodySmall,
                            color = mutedColor,
                        )
                        Spacer(Modifier.height(22.dp))
                    }
                }

                item { SectionLabel("Account") }

                item {
                    MccCard {
                        Column {
                            DetailRow(Icons.Outlined.AlternateEmail, "Email", data.user?.email)
                            DetailRow(Icons.Outlined.Badge, "Role", data.user?.role)
                            DetailRow(
                                Icons.Outlined.WorkOutline,
                                "Position",
                                data.employee?.position,
                                last = true,
                            )
                        }
                    }
                }

                item {
                    Spacer(Modifier.height(4.dp))
                    SectionLabel("Attendance summary")
                }

                item {
                    MccCard {
                        Column {
                            DetailRow(Icons.Outlined.Badge, "Days present", data.stats.presentDays.toString())
                            DetailRow(Icons.Outlined.Badge, "Days late", data.stats.lateDays.toString())
                            DetailRow(Icons.Outlined.Badge, "Days absent", data.stats.absentDays.toString())
                            DetailRow(Icons.Outlined.Badge, "Total hours", data.stats.totalHours, last = true)
                        }
                    }
                }

                item {
                    Spacer(Modifier.height(10.dp))
                    OutlinedButton(
                        onClick = { confirmSignOut = true },
                        shape = MaterialTheme.shapes.small,
                        modifier = Modifier
                            .fillMaxWidth()
                            .height(50.dp),
                    ) {
                        Icon(Icons.Outlined.Logout, contentDescription = null, modifier = Modifier.size(18.dp))
                        Text("Sign out", modifier = Modifier.padding(start = 8.dp))
                    }
                }

                item {
                    Text(
                        text = "MCC Payroll v${BuildConfig.VERSION_NAME}",
                        style = MaterialTheme.typography.bodySmall,
                        color = mutedColor,
                        modifier = Modifier
                            .fillMaxWidth()
                            .padding(top = 8.dp),
                    )
                }
            }
        }
    }
}

@Composable
private fun DetailRow(
    icon: ImageVector,
    label: String,
    value: String?,
    last: Boolean = false,
) {
    Row(
        modifier = Modifier.padding(horizontal = 16.dp, vertical = 14.dp),
        verticalAlignment = Alignment.CenterVertically,
    ) {
        Icon(
            icon,
            contentDescription = null,
            tint = mutedColor,
            modifier = Modifier.size(18.dp),
        )
        Text(
            text = label,
            style = MaterialTheme.typography.bodyMedium,
            color = mutedColor,
            modifier = Modifier
                .padding(start = 12.dp)
                .weight(1f),
        )
        Text(
            text = value?.takeIf { it.isNotBlank() } ?: "—",
            style = MaterialTheme.typography.bodyMedium,
            color = MaterialTheme.colorScheme.onSurface,
            maxLines = 1,
            overflow = TextOverflow.Ellipsis,
        )
    }

    if (!last) {
        Box(
            Modifier
                .padding(start = 46.dp)
                .fillMaxWidth()
                .height(1.dp)
                .background(MaterialTheme.colorScheme.outline)
        )
    }
}
