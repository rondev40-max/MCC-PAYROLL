package com.mcc.payroll.ui.theme

import androidx.compose.ui.graphics.Color

/**
 * Palette lifted from the web portal so the app and the site read as one system.
 * Brand blue is identical (#2563EB); the neutrals carry the same slight blue
 * bias rather than being pure greys.
 */

// Brand
val Brand = Color(0xFF2563EB)
val BrandDark = Color(0xFF1D4ED8)
val BrandLight = Color(0xFFEEF4FF)
val BrandOnLight = Color(0xFF1E3A8A)

// Semantic — kept separate from the brand hue so "paid" never reads as "primary"
val Success = Color(0xFF059669)
val SuccessContainer = Color(0xFFDCFCE7)
val Warning = Color(0xFFD97706)
val WarningContainer = Color(0xFFFEF3C7)
val Danger = Color(0xFFDC2626)
val DangerContainer = Color(0xFFFEE2E2)

// Light neutrals
val LightBackground = Color(0xFFF6F8FB)
val LightSurface = Color(0xFFFFFFFF)
val LightSurfaceVariant = Color(0xFFEEF2F7)
val LightOutline = Color(0xFFE6EBF2)
val LightOnSurface = Color(0xFF0F1729)
val LightOnSurfaceVariant = Color(0xFF4B5A70)
val LightMuted = Color(0xFF8494A9)

// Dark neutrals — rebuilt for contrast rather than inverted
val DarkBackground = Color(0xFF0B0F16)
val DarkSurface = Color(0xFF141A24)
val DarkSurfaceVariant = Color(0xFF1A212D)
val DarkOutline = Color(0xFF222B3A)
val DarkOnSurface = Color(0xFFE8EDF5)
val DarkOnSurfaceVariant = Color(0xFF9AABC2)
val DarkMuted = Color(0xFF6C7F96)
val DarkBrand = Color(0xFF4D82F3)
val DarkSuccess = Color(0xFF34D399)
val DarkWarning = Color(0xFFFBBF24)
val DarkDanger = Color(0xFFF87171)
