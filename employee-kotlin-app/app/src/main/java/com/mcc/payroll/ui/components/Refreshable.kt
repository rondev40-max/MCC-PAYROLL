package com.mcc.payroll.ui.components

import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.material.ExperimentalMaterialApi
import androidx.compose.material.pullrefresh.PullRefreshIndicator
import androidx.compose.material.pullrefresh.pullRefresh
import androidx.compose.material.pullrefresh.rememberPullRefreshState
import androidx.compose.material3.MaterialTheme
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier

/**
 * Wraps a scrollable screen in pull-to-refresh.
 *
 * The ViewModel has supported refreshing since the first version — every loader
 * takes a `refresh` flag and sets a `refreshing` state — but nothing on screen
 * ever triggered it, so the only way to get fresh figures was to kill the app.
 * This is the missing half.
 *
 * Material 2's modifier rather than Material 3's PullToRefreshBox: this project
 * is pinned to compose-bom 2024.01.00, which resolves material3 to 1.1.2, and
 * PullToRefreshBox does not exist until 1.3.
 */
@OptIn(ExperimentalMaterialApi::class)
@Composable
fun Refreshable(
    refreshing: Boolean,
    onRefresh: () -> Unit,
    modifier: Modifier = Modifier,
    content: @Composable () -> Unit,
) {
    val state = rememberPullRefreshState(refreshing = refreshing, onRefresh = onRefresh)

    Box(
        modifier = modifier
            .fillMaxSize()
            .pullRefresh(state)
    ) {
        content()

        PullRefreshIndicator(
            refreshing = refreshing,
            state = state,
            modifier = Modifier.align(Alignment.TopCenter),
            backgroundColor = MaterialTheme.colorScheme.surface,
            contentColor = MaterialTheme.colorScheme.primary,
        )
    }
}
