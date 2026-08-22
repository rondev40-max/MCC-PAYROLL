package com.mcc.payroll.ui

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.AccessTime
import androidx.compose.material.icons.outlined.Campaign
import androidx.compose.material.icons.outlined.CheckCircle
import androidx.compose.material.icons.outlined.Cancel
import androidx.compose.material.icons.outlined.ReceiptLong
import androidx.compose.material.icons.outlined.WatchLater
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import com.mcc.payroll.data.remote.Announcement
import com.mcc.payroll.data.remote.Payslip
import com.mcc.payroll.data.remote.Stats
import com.mcc.payroll.ui.components.EmptyState
import com.mcc.payroll.ui.components.ErrorState
import com.mcc.payroll.ui.components.IconTile
import com.mcc.payroll.ui.components.LoadingState
import com.mcc.payroll.ui.components.Refreshable
import com.mcc.payroll.ui.components.MccCard
import com.mcc.payroll.ui.components.SectionLabel
import com.mcc.payroll.ui.theme.mutedColor
import com.mcc.payroll.ui.theme.statusColors
import com.mcc.payroll.viewmodel.EmployeeViewModel

@Composable
fun DashboardScreen(
    viewModel: EmployeeViewModel,
    onSeeAllAnnouncements: () -> Unit = {},
) {
    val state by viewModel.homeState.collectAsState()
    val data by viewModel.home.collectAsState()
    val cachedName by viewModel.cachedName.collectAsState()

    LaunchedEffect(Unit) { if (data.user == null) viewModel.loadHome() }

    when {
        state.loading -> LoadingState()
        state.error != null -> ErrorState(state.error!!, onRetry = { viewModel.loadHome() })
        else -> Refreshable(
            refreshing = state.refreshing,
            onRefresh = { viewModel.loadHome(refresh = true) },
        ) {
            DashboardContent(
                name = data.user?.name ?: cachedName,
                position = data.employee?.position,
                stats = data.stats,
                payslips = data.payslips,
                announcements = data.announcements,
                onSeeAllAnnouncements = onSeeAllAnnouncements,
            )
        }
    }
}

@Composable
private fun DashboardContent(
    name: String?,
    position: String?,
    stats: Stats,
    payslips: List<Payslip>,
    announcements: List<Announcement>,
    onSeeAllAnnouncements: () -> Unit,
) {
    val status = statusColors

    LazyColumn(
        modifier = Modifier
            .fillMaxSize(),
        contentPadding = PaddingValues(start = 20.dp, end = 20.dp, top = 24.dp, bottom = 32.dp),
        verticalArrangement = Arrangement.spacedBy(22.dp),
    ) {

        // ── Greeting ────────────────────────────────────────────────────────
        item {
            Column {
                Text(
                    text = Format.greeting() + ",",
                    style = MaterialTheme.typography.bodyMedium,
                    color = mutedColor,
                )
                Text(
                    text = Format.firstName(name),
                    style = MaterialTheme.typography.headlineMedium,
                    color = MaterialTheme.colorScheme.onBackground,
                )
                if (!position.isNullOrBlank()) {
                    Text(
                        text = position,
                        style = MaterialTheme.typography.bodySmall,
                        color = mutedColor,
                        modifier = Modifier.padding(top = 2.dp),
                    )
                }
            }
        }

        // ── Today ───────────────────────────────────────────────────────────
        item {
            Column {
                SectionLabel("Today")
                MccCard {
                    Row(
                        modifier = Modifier.padding(18.dp),
                        verticalAlignment = Alignment.CenterVertically,
                    ) {
                        IconTile(Icons.Outlined.AccessTime, MaterialTheme.colorScheme.primary, size = 46)
                        Spacer(Modifier.width(14.dp))
                        Row(modifier = Modifier.weight(1f)) {
                            TimeBlock("Clock in", stats.todayTimeIn, Modifier.weight(1f))
                            TimeBlock("Clock out", stats.todayTimeOut, Modifier.weight(1f))
                            TimeBlock("Hours", stats.todayHours, Modifier.weight(1f))
                        }
                    }
                }
            }
        }

        // ── Attendance summary ──────────────────────────────────────────────
        item {
            Column {
                SectionLabel("This period")
                Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                    StatTile("Present", stats.presentDays.toString(), Icons.Outlined.CheckCircle, status.success, Modifier.weight(1f))
                    StatTile("Late", stats.lateDays.toString(), Icons.Outlined.WatchLater, status.warning, Modifier.weight(1f))
                    StatTile("Absent", stats.absentDays.toString(), Icons.Outlined.Cancel, status.danger, Modifier.weight(1f))
                }
                Spacer(Modifier.height(12.dp))
                MccCard {
                    Row(
                        modifier = Modifier.padding(horizontal = 18.dp, vertical = 14.dp),
                        verticalAlignment = Alignment.CenterVertically,
                    ) {
                        Text(
                            text = "Total hours logged",
                            style = MaterialTheme.typography.bodyMedium,
                            color = mutedColor,
                            modifier = Modifier.weight(1f),
                        )
                        Text(
                            text = stats.totalHours,
                            style = MaterialTheme.typography.titleLarge,
                            color = MaterialTheme.colorScheme.onSurface,
                        )
                    }
                }
            }
        }

        // ── Latest payslip ──────────────────────────────────────────────────
        item { SectionLabel("Latest payslip") }

        if (payslips.isEmpty()) {
            item {
                MccCard {
                    EmptyState(
                        icon = Icons.Outlined.ReceiptLong,
                        title = "No payslips yet",
                        detail = "Released payslips will appear here.",
                    )
                }
            }
        } else {
            item { PayslipCard(payslips.first(), highlight = true) }
        }

        // ── Announcements ───────────────────────────────────────────────────
        item {
            Row(verticalAlignment = Alignment.CenterVertically) {
                SectionLabel("Announcements", Modifier.weight(1f))
                // The dashboard payload is capped at five by the server, so
                // there is always a chance more exist than are shown here.
                TextButton(onClick = onSeeAllAnnouncements) { Text("See all") }
            }
        }

        if (announcements.isEmpty()) {
            item {
                MccCard {
                    EmptyState(
                        icon = Icons.Outlined.Campaign,
                        title = "Nothing new",
                        detail = "Notices from the HR office will show up here.",
                    )
                }
            }
        } else {
            items(announcements, key = { it.id ?: it.hashCode() }) { announcement ->
                AnnouncementCard(announcement)
            }
        }
    }
}

@Composable
private fun TimeBlock(label: String, value: String, modifier: Modifier = Modifier) {
    Column(modifier = modifier) {
        Text(label, style = MaterialTheme.typography.bodySmall, color = mutedColor)
        Text(
            text = value,
            style = MaterialTheme.typography.titleMedium,
            color = MaterialTheme.colorScheme.onSurface,
            maxLines = 1,
            overflow = TextOverflow.Ellipsis,
        )
    }
}

@Composable
private fun StatTile(
    label: String,
    value: String,
    icon: ImageVector,
    tint: Color,
    modifier: Modifier = Modifier,
) {
    MccCard(modifier = modifier) {
        Column(modifier = Modifier.padding(14.dp)) {
            Icon(icon, contentDescription = null, tint = tint, modifier = Modifier.size(18.dp))
            Text(
                text = value,
                style = MaterialTheme.typography.headlineSmall,
                color = MaterialTheme.colorScheme.onSurface,
                modifier = Modifier.padding(top = 8.dp),
            )
            Text(label, style = MaterialTheme.typography.bodySmall, color = mutedColor)
        }
    }
}

@Composable
fun PayslipCard(payslip: Payslip, highlight: Boolean = false) {
    MccCard {
        Column(modifier = Modifier.padding(18.dp)) {
            Row(verticalAlignment = Alignment.CenterVertically) {
                IconTile(Icons.Outlined.ReceiptLong, MaterialTheme.colorScheme.primary)
                Spacer(Modifier.width(12.dp))
                Column(modifier = Modifier.weight(1f)) {
                    Text(
                        text = payslip.payPeriod?.takeIf { it.isNotBlank() }
                            ?: Format.date(payslip.sentAt),
                        style = MaterialTheme.typography.titleMedium,
                        color = MaterialTheme.colorScheme.onSurface,
                        maxLines = 1,
                        overflow = TextOverflow.Ellipsis,
                    )
                    Text(
                        text = "Issued " + Format.date(payslip.sentAt),
                        style = MaterialTheme.typography.bodySmall,
                        color = mutedColor,
                    )
                }
            }

            if (highlight) {
                Spacer(Modifier.height(16.dp))
                Text("Net pay", style = MaterialTheme.typography.bodySmall, color = mutedColor)
                Text(
                    text = Format.money(payslip.totalHonorarium),
                    style = MaterialTheme.typography.displaySmall,
                    color = MaterialTheme.colorScheme.primary,
                )
            } else {
                Spacer(Modifier.height(12.dp))
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Text(
                        text = "Net pay",
                        style = MaterialTheme.typography.bodySmall,
                        color = mutedColor,
                        modifier = Modifier.weight(1f),
                    )
                    Text(
                        text = Format.money(payslip.totalHonorarium),
                        style = MaterialTheme.typography.titleLarge,
                        color = MaterialTheme.colorScheme.primary,
                    )
                }
            }

            if (!payslip.designation.isNullOrBlank() || !payslip.employeeType.isNullOrBlank()) {
                Spacer(Modifier.height(10.dp))
                Text(
                    text = listOfNotNull(
                        payslip.employeeType?.takeIf { it.isNotBlank() },
                        payslip.designation?.takeIf { it.isNotBlank() },
                    ).joinToString(" · "),
                    style = MaterialTheme.typography.bodySmall,
                    color = mutedColor,
                )
            }
        }
    }
}

@Composable
private fun AnnouncementCard(announcement: Announcement) {
    MccCard {
        Row(modifier = Modifier.padding(16.dp)) {
            IconTile(Icons.Outlined.Campaign, statusColors.warning, size = 38)
            Spacer(Modifier.width(12.dp))
            Column {
                Text(
                    text = announcement.title.orEmpty(),
                    style = MaterialTheme.typography.titleMedium,
                    color = MaterialTheme.colorScheme.onSurface,
                )
                Text(
                    text = announcement.message.orEmpty(),
                    style = MaterialTheme.typography.bodySmall,
                    color = mutedColor,
                    maxLines = 3,
                    overflow = TextOverflow.Ellipsis,
                    modifier = Modifier.padding(top = 3.dp),
                )
                Text(
                    text = Format.date(announcement.createdAt),
                    style = MaterialTheme.typography.bodySmall,
                    color = mutedColor,
                    modifier = Modifier.padding(top = 6.dp),
                )
            }
        }
    }
}
