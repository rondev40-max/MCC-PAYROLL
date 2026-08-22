package com.mcc.payroll.ui

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.CalendarMonth
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.unit.dp
import com.mcc.payroll.data.remote.Attendance
import com.mcc.payroll.ui.components.EmptyState
import com.mcc.payroll.ui.components.ErrorState
import com.mcc.payroll.ui.components.IconTile
import com.mcc.payroll.ui.components.LoadingState
import com.mcc.payroll.ui.components.Refreshable
import com.mcc.payroll.ui.components.MccCard
import com.mcc.payroll.ui.components.SectionLabel
import com.mcc.payroll.ui.components.StatusChip
import com.mcc.payroll.ui.theme.mutedColor
import com.mcc.payroll.ui.theme.statusColors
import com.mcc.payroll.viewmodel.EmployeeViewModel

@Composable
fun AttendanceScreen(viewModel: EmployeeViewModel) {
    val state by viewModel.attendanceState.collectAsState()
    val data by viewModel.attendance.collectAsState()

    LaunchedEffect(Unit) { if (data.attendances.isEmpty()) viewModel.loadAttendance() }

    when {
        state.loading -> LoadingState()
        state.error != null -> ErrorState(state.error!!, onRetry = { viewModel.loadAttendance() })
        else -> Refreshable(
            refreshing = state.refreshing,
            onRefresh = { viewModel.loadAttendance(refresh = true) },
        ) {
            LazyColumn(
                modifier = Modifier.fillMaxSize(),
                contentPadding = PaddingValues(start = 20.dp, end = 20.dp, top = 24.dp, bottom = 32.dp),
                verticalArrangement = Arrangement.spacedBy(12.dp),
            ) {
                item {
                    Column {
                        Text(
                            text = "Attendance",
                            style = MaterialTheme.typography.headlineMedium,
                            color = MaterialTheme.colorScheme.onBackground,
                        )
                        Text(
                            text = "${data.attendances.size} record" +
                                (if (data.attendances.size == 1) "" else "s") +
                                " · ${data.stats.totalHours} logged",
                            style = MaterialTheme.typography.bodySmall,
                            color = mutedColor,
                        )
                        Spacer(Modifier.height(18.dp))
                        SectionLabel("Log")
                    }
                }

                if (data.attendances.isEmpty()) {
                    item {
                        MccCard {
                            EmptyState(
                                icon = Icons.Outlined.CalendarMonth,
                                title = "No attendance recorded",
                                detail = "Check-ins logged at the terminal will appear here.",
                            )
                        }
                    }
                } else {
                    items(data.attendances, key = { it.id ?: it.hashCode() }) { record ->
                        AttendanceRow(record)
                    }
                }
            }
        }
    }
}

@Composable
private fun AttendanceRow(record: Attendance) {
    val status = statusColors
    val label = record.status?.replaceFirstChar { it.uppercase() } ?: "Unknown"

    // Colour is decided by the status the server actually sent, with an explicit
    // fallback — an unrecognised value must not silently render as "present".
    val tint: Color = when (record.status?.lowercase()) {
        "present" -> status.success
        "late" -> status.warning
        "absent" -> status.danger
        else -> mutedColor
    }

    MccCard {
        Row(
            modifier = Modifier.padding(16.dp),
            verticalAlignment = Alignment.CenterVertically,
        ) {
            IconTile(Icons.Outlined.CalendarMonth, tint, size = 40)
            Spacer(Modifier.width(14.dp))

            Column(modifier = Modifier.weight(1f)) {
                Text(
                    text = Format.shortDate(record.date),
                    style = MaterialTheme.typography.titleMedium,
                    color = MaterialTheme.colorScheme.onSurface,
                )
                Text(
                    text = "${record.timeIn ?: "—"}  →  ${record.timeOut ?: "—"}",
                    style = MaterialTheme.typography.bodySmall,
                    color = mutedColor,
                )
            }

            Column(horizontalAlignment = Alignment.End) {
                StatusChip(label, tint)
                Text(
                    text = record.hoursRendered?.let { "%.1fh".format(it) } ?: "—",
                    style = MaterialTheme.typography.bodySmall,
                    color = mutedColor,
                    modifier = Modifier.padding(top = 6.dp),
                )
            }
        }
    }
}
