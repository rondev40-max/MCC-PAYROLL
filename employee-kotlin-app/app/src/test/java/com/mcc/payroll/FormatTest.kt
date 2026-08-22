package com.mcc.payroll

import com.mcc.payroll.ui.Format
import org.junit.Assert.assertEquals
import org.junit.Test

/**
 * Format is pure and drives every figure the employee reads, so it is worth
 * pinning. The date cases matter most: Laravel sends ISO-8601 for timestamps
 * but plain yyyy-MM-dd for date columns, and both reach these functions.
 */
class FormatTest {

    @Test
    fun `money is grouped and always two decimals`() {
        assertEquals("₱18,450.00", Format.money(18450.0))
        assertEquals("₱0.00", Format.money(0.0))
        assertEquals("₱1,234.57", Format.money(1234.567))
    }

    @Test
    fun `money treats a missing amount as zero rather than blank`() {
        assertEquals("₱0.00", Format.money(null))
    }

    @Test
    fun `date parses both shapes Laravel actually sends`() {
        assertEquals("18 Aug 2026", Format.date("2026-08-18"))
        assertEquals("18 Aug 2026", Format.date("2026-08-18 14:30:00"))
        assertEquals("18 Aug 2026", Format.date("2026-08-18T14:30:00"))
    }

    @Test
    fun `an unparseable date is returned untouched, never as an epoch`() {
        assertEquals("sometime", Format.date("sometime"))
        assertEquals("—", Format.date(null))
        assertEquals("—", Format.date(""))
    }

    @Test
    fun `initials handle one name, two names and nothing`() {
        assertEquals("JB", Format.initials("Jaylian Bacolod"))
        assertEquals("JA", Format.initials("Jaylian"))
        assertEquals("?", Format.initials(null))
        assertEquals("?", Format.initials("   "))
    }

    @Test
    fun `initials use first and last of a long name, not the middle`() {
        assertEquals("JR", Format.initials("Juan Miguel Dela Cruz Reyes"))
    }

    @Test
    fun `firstName falls back to a greeting that still reads correctly`() {
        assertEquals("Jaylian", Format.firstName("Jaylian Bacolod"))
        assertEquals("there", Format.firstName(null))
        assertEquals("there", Format.firstName("  "))
    }
}
