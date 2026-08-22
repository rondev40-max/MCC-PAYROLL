package com.mcc.payroll

import com.mcc.payroll.ui.Format
import org.junit.Assert.assertEquals
import org.junit.Test

/**
 * Money formatting.
 *
 * Java's NumberFormat rounds HALF_EVEN by default; PHP's number_format rounds
 * HALF_UP. Format.money is pinned to HALF_UP so the app cannot print a centavo
 * different from the web portal for the same payslip.
 *
 * Exact parity with PHP for every input is not achievable and is not attempted:
 * a literal like 9.995 is held as 9.99499999… in IEEE-754, so HALF_UP correctly
 * yields 9.99 while PHP's pre-rounding compensation reports 10.00. That gap is
 * unreachable here in any case — payslip amounts arrive from a decimal(12,2)
 * column and already carry exactly two decimals, so nothing needs rounding at
 * the centavo on the way in.
 */
class MoneyRoundingTest {

    @Test
    fun `rounds half up rather than to even, matching the server`() {
        // HALF_EVEN would give ₱0.12 here, and disagree with the web portal.
        assertEquals("₱0.13", Format.money(0.125))
        assertEquals("₱0.38", Format.money(0.375))
    }

    @Test
    fun `values that already carry two decimals pass through untouched`() {
        // This is the only shape the API actually sends.
        assertEquals("₱18,450.75", Format.money(18450.75))
        assertEquals("₱0.01", Format.money(0.01))
    }

    @Test
    fun `large payroll figures keep their grouping`() {
        assertEquals("₱1,000,000.00", Format.money(1_000_000.0))
    }

    @Test
    fun `negative adjustments are not silently shown as positive`() {
        assertEquals("₱-250.00", Format.money(-250.0))
    }
}
