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

## Paying from the attendance checker's DTR

An alternative to web Time In / Time Out that needs no employee phones: the department's attendance checker keeps the register, and payroll reads it. Turn it on in **System Settings → Payroll → Pay from the attendance checker's DTR**. It is off by default.

Daily use by the checker:

1. Open the register, keep today's date, press **Mark all present**. Everyone in the department with no entry for that day gets 8:00–12:00 and 1:00–5:00. Existing entries are never changed, and part-time instructors are skipped. Sundays, holidays and future days are refused.
2. Edit only the exceptions: late arrivals, early departures, absences, leave, official business.
3. **Fill official hours** in an employee's editor fills that person's empty weekdays up to today. Nothing is saved until **Save entries**.

What Send Payslips does when the setting is on (`App\Support\CheckerDtrPayroll`):

- Covers full-time instructors (hourly rate), staff and utility workers (daily rate). Part-time instructors are paid for their teaching load from their timesheet; watchmen and admin personnel have no register. Anyone already on web Time In / Time Out payroll stays on it.
- A day is worth 480 minutes minus DTR undertime (late arrival and early departure). Half day: 240 minus undertime. Official business: a full day. Absent, leave and holiday: unpaid, matching the timesheets, which leave holidays out. Time before 8:00 or after 5:00 is not paid.
- Full-time: paid minutes ÷ 60 × hourly rate. Staff and utility: paid minutes ÷ 480 × daily rate. Rates and deductions still come from the timesheet; the stored timesheet is not changed.
- Needs one whole cutoff (1–15 or 16–end). Every Monday–Friday that is not a holiday must have an entry, and no entry may be half-filled. Otherwise nothing is sent and the error lists each employee and date to finish.
- The payslip history keeps the day-by-day DTR used (`attendance_snapshot.source = checker_dtr`), and the payslip email's day grid shows the paid hours per date.

The register identifies a person by the timesheet's master-list `employee_id` when set, otherwise by the timesheet row id, plus department and employee type. Link timesheets to the master list so a person keeps one register across cutoffs.

## Recommended next steps

Define shift schedules and break rules per employee group; add employee correction requests with supervisor approval; add overtime/leave approvals; then add a payroll batch lock and missed-punch reminders. An optional campus network or location check can supplement server timestamps if on-site attendance is required.

## Verification

`php artisan test tests/Feature/AttendanceClockTest.php tests/Feature/EmployeePortalIdentityTest.php tests/Unit/Support/WageLiquidationTest.php`

Tests use an in-memory SQLite database and fake email delivery. They cover server timestamps, duplicate/stale punches, identity isolation, overnight shifts, exception review, cutoff activation, hourly and daily payroll, deductions, email amounts, and immutable payroll snapshots.
