package com.mcc.payroll.ui.theme

import android.app.Activity
import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.darkColorScheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.runtime.SideEffect
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.luminance
import androidx.compose.ui.graphics.toArgb
import androidx.compose.ui.platform.LocalView
import androidx.core.view.WindowCompat

private val LightColors = lightColorScheme(
    primary = Brand,
    onPrimary = Color.White,
    primaryContainer = BrandLight,
    onPrimaryContainer = BrandOnLight,
    secondary = LightOnSurfaceVariant,
    onSecondary = Color.White,
    background = LightBackground,
    onBackground = LightOnSurface,
    surface = LightSurface,
    onSurface = LightOnSurface,
    surfaceVariant = LightSurfaceVariant,
    onSurfaceVariant = LightOnSurfaceVariant,
    outline = LightOutline,
    outlineVariant = LightOutline,
    error = Danger,
    onError = Color.White,
    errorContainer = DangerContainer,
    onErrorContainer = Color(0xFF7F1D1D),
)

private val DarkColors = darkColorScheme(
    primary = DarkBrand,
    onPrimary = Color(0xFF06121F),
    primaryContainer = Color(0xFF1B2E52),
    onPrimaryContainer = Color(0xFFD5E2FD),
    secondary = DarkOnSurfaceVariant,
    onSecondary = Color(0xFF06121F),
    background = DarkBackground,
    onBackground = DarkOnSurface,
    surface = DarkSurface,
    onSurface = DarkOnSurface,
    surfaceVariant = DarkSurfaceVariant,
    onSurfaceVariant = DarkOnSurfaceVariant,
    outline = DarkOutline,
    outlineVariant = DarkOutline,
    error = DarkDanger,
    onError = Color(0xFF3F0A0A),
    errorContainer = Color(0xFF4C1D1D),
    onErrorContainer = Color(0xFFFECACA),
)

/** Muted text colour for the active theme — [MaterialTheme] has no slot for it. */
val mutedColor: Color
    @Composable get() = if (isSystemInDarkTheme()) DarkMuted else LightMuted

/** Semantic colours resolved for the active theme. */
data class StatusColors(val success: Color, val warning: Color, val danger: Color)

val statusColors: StatusColors
    @Composable get() = if (isSystemInDarkTheme()) {
        StatusColors(DarkSuccess, DarkWarning, DarkDanger)
    } else {
        StatusColors(Success, Warning, Danger)
    }

@Composable
fun MccPayrollTheme(
    darkTheme: Boolean = isSystemInDarkTheme(),
    content: @Composable () -> Unit,
) {
    val colors = if (darkTheme) DarkColors else LightColors
    val view = LocalView.current

    if (!view.isInEditMode) {
        SideEffect {
            val window = (view.context as Activity).window
            window.statusBarColor = colors.background.toArgb()
            // Icon tint follows the bar's luminance, otherwise the status bar
            // goes white-on-white the moment the theme flips.
            WindowCompat.getInsetsController(window, view)
                .isAppearanceLightStatusBars = colors.background.luminance() > 0.5f
        }
    }

    MaterialTheme(
        colorScheme = colors,
        typography = MccTypography,
        shapes = MccShapes,
        content = content,
    )
}
