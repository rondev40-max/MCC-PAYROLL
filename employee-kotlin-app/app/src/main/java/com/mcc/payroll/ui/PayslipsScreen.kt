package com.mcc.payroll.ui

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.ReceiptLong
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.mcc.payroll.ui.components.EmptyState
import com.mcc.payroll.ui.components.ErrorState
import com.mcc.payroll.ui.components.LoadingState
import com.mcc.payroll.ui.components.MccCard
import com.mcc.payroll.ui.theme.mutedColor
import com.mcc.payroll.viewmodel.EmployeeViewModel

@Composable
fun PayslipsScreen(viewModel: EmployeeViewModel) {
    val state by viewModel.payslipsState.collectAsState()
    val payslips by viewModel.payslips.collectAsState()

    LaunchedEffect(Unit) { if (payslips.isEmpty()) viewModel.loadPayslips() }

    when {
        state.loading -> LoadingState()
        state.error != null -> ErrorState(state.error!!, onRetry = { viewModel.loadPayslips() })
        else -> LazyColumn(
            modifier = Modifier.fillMaxSize(),
            contentPadding = PaddingValues(start = 20.dp, end = 20.dp, top = 24.dp, bottom = 32.dp),
            verticalArrangement = Arrangement.spacedBy(14.dp),
        ) {
            item {
                Column {
                    Text(
                        text = "Payslips",
                        style = MaterialTheme.typography.headlineMedium,
                        color = MaterialTheme.colorScheme.onBackground,
                    )
                    Text(
                        text = if (payslips.isEmpty()) {
                            "Nothing released yet"
                        } else {
                            "${payslips.size} released · newest first"
                        },
                        style = MaterialTheme.typography.bodySmall,
                        color = mutedColor,
                    )
                }
            }

            // Year-to-date total. The web portal never shows this and it is the
            // number people actually open the app to find.
            if (payslips.isNotEmpty()) {
                item {
                    MccCard {
                        Row(
                            modifier = Modifier.padding(horizontal = 18.dp, vertical = 16.dp),
                            verticalAlignment = Alignment.CenterVertically,
                        ) {
                            Column(modifier = Modifier.weight(1f)) {
                                Text(
                                    text = "Total received",
                                    style = MaterialTheme.typography.bodySmall,
                                    color = mutedColor,
                                )
                                Text(
                                    text = "across ${payslips.size} payslip" + if (payslips.size == 1) "" else "s",
                                    style = MaterialTheme.typography.bodySmall,
                                    color = mutedColor,
                                )
                            }
                            Text(
                                text = Format.money(payslips.sumOf { it.totalHonorarium ?: 0.0 }),
                                style = MaterialTheme.typography.headlineSmall,
                                color = MaterialTheme.colorScheme.primary,
                            )
                        }
                    }
                }
            }

            if (payslips.isEmpty()) {
                item {
                    MccCard {
                        EmptyState(
                            icon = Icons.Outlined.ReceiptLong,
                            title = "No payslips yet",
                            detail = "Once payroll releases a payslip for you, it stays here permanently.",
                        )
                    }
                }
            } else {
                items(payslips, key = { it.id ?: it.hashCode() }) { payslip ->
                    PayslipCard(payslip)
                }
            }
        }
    }
}
