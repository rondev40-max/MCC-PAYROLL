# Web attendance and automatic payroll

Deploy the code and run `php artisan migrate` before opening the employee portal. The migration adds attendance sessions, an employee activation date, and a payslip attendance snapshot. No production database is changed by the development tests.

## Workflow

1. Match each employee account to the employee master list by email or an explicit employee ID.
2. Employees open **Attendance** in the web portal and use **Time in / Time out**. The server records Manila time. Clock out for unpaid breaks and back in afterward.
3. Admins open **History Records → Attendance Payroll** to inspect punches, hours, day equivalents and gross/net estimates. The existing payroll row supplies the rate and deductions. Its employee ID or email must match the master list.
4. Choose the **1st or 16th** as the activation date for each employee. Verify all attendance for that cutoff before activation; missing days contribute zero. Activation applies to that cutoff and every later cutoff. Historical manual payroll before activation stays manual.
5. Use the existing **Send Payslips** action for a complete cutoff. For enabled employees, the server replaces scheduled units with recorded duty units, calculates gross pay, and applies the existing deductions. Payslip emails and saved history use the same calculation. The old editable schedule tables remain rate/deduction setup screens; use Attendance Payroll for the actual attendance estimate.

## Calculation rules

- Full-time and part-time: recorded hours × configured hourly rate.
- Staff, utility, watchman and admin personnel: sum of daily `min(recorded hours / 8, 1)` × configured daily rate. `config/attendance.php` controls the standard day length.
- Breaks are gaps between sessions; no guessed lunch deduction.
- Overnight duty belongs to the date of time in, including across cutoffs/months.
- Open sessions and sessions longer than 18 hours block payroll sending for that employee's cutoff. An admin can approve verified hours or exclude the session, with a required reason. Original punch timestamps remain unchanged; reviewer ID, reason and approved duration are retained.
- Attendance is stored separately from the checker's legacy attendance records because those records can use payroll-row IDs. The employee dashboard combines daily displays, giving web punches precedence on dates with web records. Do not add legacy checker hours to web hours again.
- Released payslips store a snapshot of the sessions and daily hours used. Later attendance changes do not rewrite those payslips.
- This implementation does not calculate overtime premiums, holiday premiums, paid leave, shift-based lateness, or statutory contribution rates. Rates and deductions still come from the payroll setup. Hourly duty, including extended hours, uses the configured base rate; daily units cap at one regular day.

## Recommended next steps

Define shift schedules and break rules per employee group; add employee correction requests with supervisor approval; add overtime/leave approvals; then add a payroll batch lock and missed-punch reminders. An optional campus network or location check can supplement server timestamps if on-site attendance is required.

## Verification

`php artisan test tests/Feature/AttendanceClockTest.php tests/Feature/EmployeePortalIdentityTest.php tests/Unit/Support/WageLiquidationTest.php`

Tests use an in-memory SQLite database and fake email delivery. They cover server timestamps, duplicate/stale punches, identity isolation, overnight shifts, exception review, cutoff activation, hourly and daily payroll, deductions, email amounts, and immutable payroll snapshots.
