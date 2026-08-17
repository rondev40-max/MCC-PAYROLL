package com.mcc.payroll.ui

import java.text.NumberFormat
import java.text.SimpleDateFormat
import java.util.Calendar
import java.util.Date
import java.util.Locale

/**
 * Display formatting. Kept in one file so a peso figure looks identical on
 * every screen, and so date parsing has a single place to be lenient.
 */
object Format {

    private val peso: NumberFormat = NumberFormat.getNumberInstance(Locale.US).apply {
        minimumFractionDigits = 2
        maximumFractionDigits = 2
    }

    /** ₱18,450.00 — grouped and always two decimals, so columns line up. */
    fun money(value: Double?): String = "₱" + peso.format(value ?: 0.0)

    /**
     * Laravel serialises timestamps as ISO-8601 but `date` columns come back as
     * plain `yyyy-MM-dd`, so both shapes have to parse. Anything unrecognised is
     * returned untouched rather than shown as "null" or an epoch date.
     */
    private val patterns = listOf(
        "yyyy-MM-dd'T'HH:mm:ss.SSSSSS'Z'",
        "yyyy-MM-dd'T'HH:mm:ss'Z'",
        "yyyy-MM-dd'T'HH:mm:ss",
        "yyyy-MM-dd HH:mm:ss",
        "yyyy-MM-dd",
    )

    private fun parse(raw: String?): Date? {
        if (raw.isNullOrBlank()) return null
        for (pattern in patterns) {
            try {
                return SimpleDateFormat(pattern, Locale.US).parse(raw)
            } catch (_: Exception) {
                // Try the next shape.
            }
        }
        return null
    }

    /** "18 Aug 2026" */
    fun date(raw: String?): String {
        val parsed = parse(raw) ?: return raw?.takeIf { it.isNotBlank() } ?: "—"
        return SimpleDateFormat("d MMM yyyy", Locale.US).format(parsed)
    }

    /** "Tue, 18 Aug" — for list rows where the year is noise. */
    fun shortDate(raw: String?): String {
        val parsed = parse(raw) ?: return raw?.takeIf { it.isNotBlank() } ?: "—"
        return SimpleDateFormat("EEE, d MMM", Locale.US).format(parsed)
    }

    /** Greeting that matches the time of day rather than always saying hello. */
    fun greeting(): String = when (Calendar.getInstance().get(Calendar.HOUR_OF_DAY)) {
        in 0..11 -> "Good morning"
        in 12..17 -> "Good afternoon"
        else -> "Good evening"
    }

    /** First name only — a greeting with a full legal name reads like a letter. */
    fun firstName(full: String?): String =
        full?.trim()?.split(" ")?.firstOrNull()?.takeIf { it.isNotBlank() } ?: "there"

    fun initials(full: String?): String {
        val parts = full?.trim()?.split(Regex("\\s+")).orEmpty().filter { it.isNotBlank() }
        return when {
            parts.isEmpty() -> "?"
            parts.size == 1 -> parts[0].take(2).uppercase()
            else -> (parts.first().take(1) + parts.last().take(1)).uppercase()
        }
    }
}
