package com.mcc.payroll.ui

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.outlined.ArrowBack
import androidx.compose.material.icons.outlined.Campaign
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.mcc.payroll.data.remote.Announcement
import com.mcc.payroll.ui.components.EmptyState
import com.mcc.payroll.ui.components.ErrorState
import com.mcc.payroll.ui.components.IconTile
import com.mcc.payroll.ui.components.LoadingState
import com.mcc.payroll.ui.components.MccCard
import com.mcc.payroll.ui.components.Refreshable
import com.mcc.payroll.ui.components.StatusChip
import com.mcc.payroll.ui.theme.mutedColor
import com.mcc.payroll.ui.theme.statusColors
import com.mcc.payroll.viewmodel.EmployeeViewModel

/**
 * The full notice board.
 *
 * The dashboard shows only the newest five — that is all /mobile/dashboard
 * returns — so this screen exists to reach the rest. The /mobile/announcements
 * endpoint had been declared in ApiService since the first version and never
 * called by anything.
 */
@Composable
fun AnnouncementsScreen(
    viewModel: EmployeeViewModel,
    onBack: () -> Unit,
) {
    val state by viewModel.announcementsState.collectAsState()
    val announcements by viewModel.announcements.collectAsState()

    LaunchedEffect(Unit) { if (announcements.isEmpty()) viewModel.loadAnnouncements() }

    Column(modifier = Modifier.fillMaxSize()) {
        Row(
            modifier = Modifier.padding(start = 6.dp, end = 20.dp, top = 12.dp),
            verticalAlignment = Alignment.CenterVertically,
        ) {
            IconButton(onClick = onBack) {
                Icon(Icons.AutoMirrored.Outlined.ArrowBack, contentDescription = "Back")
            }
            Text(
                text = "Announcements",
                style = MaterialTheme.typography.headlineSmall,
                color = MaterialTheme.colorScheme.onBackground,
            )
        }

        when {
            state.loading -> LoadingState()
            state.error != null -> ErrorState(state.error!!, onRetry = { viewModel.loadAnnouncements() })
            else -> Refreshable(
                refreshing = state.refreshing,
                onRefresh = { viewModel.loadAnnouncements(refresh = true) },
            ) {
                LazyColumn(
                    modifier = Modifier.fillMaxSize(),
                    contentPadding = PaddingValues(start = 20.dp, end = 20.dp, top = 8.dp, bottom = 32.dp),
                    verticalArrangement = Arrangement.spacedBy(12.dp),
                ) {
                    if (announcements.isEmpty()) {
                        item {
                            MccCard {
                                EmptyState(
                                    icon = Icons.Outlined.Campaign,
                                    title = "Nothing posted",
                                    detail = "Notices from the HR office will appear here.",
                                )
                            }
                        }
                    } else {
                        items(announcements, key = { it.id ?: it.hashCode() }) { announcement ->
                            AnnouncementRow(announcement)
                        }
                    }
                }
            }
        }
    }
}

@Composable
private fun AnnouncementRow(announcement: Announcement) {
    val status = statusColors

    // The server's `type` column is free text with a default of "general".
    // Anything unrecognised falls back to the neutral tint rather than being
    // dropped or mis-coloured as an alert.
    val tint = when (announcement.type?.lowercase()) {
        "urgent", "alert" -> status.danger
        "warning", "notice" -> status.warning
        "success" -> status.success
        else -> MaterialTheme.colorScheme.primary
    }

    MccCard {
        Column(modifier = Modifier.padding(16.dp)) {
            Row(verticalAlignment = Alignment.CenterVertically) {
                IconTile(Icons.Outlined.Campaign, tint, size = 38)
                Spacer(Modifier.width(12.dp))
                Column(modifier = Modifier.weight(1f)) {
                    Text(
                        text = announcement.title.orEmpty(),
                        style = MaterialTheme.typography.titleMedium,
                        color = MaterialTheme.colorScheme.onSurface,
                    )
                    Text(
                        text = Format.date(announcement.createdAt),
                        style = MaterialTheme.typography.bodySmall,
                        color = mutedColor,
                    )
                }
                if (!announcement.type.isNullOrBlank()) {
                    StatusChip(announcement.type.replaceFirstChar { it.uppercase() }, tint)
                }
            }

            Spacer(Modifier.size(10.dp))

            Text(
                text = announcement.message.orEmpty(),
                style = MaterialTheme.typography.bodyMedium,
                color = mutedColor,
            )
        }
    }
}
