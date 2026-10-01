<div class="alert alert-info m-3 d-print-none">
  <a href="{{ route('admin.attendance-payroll.index') }}" class="fw-bold">Attendance Payroll</a>
  shows recorded duty hours and payroll estimates. For enabled employees, Send Payslips uses those hours automatically; this table maintains scheduled hours, rates and deductions.
  @if(\App\Support\CheckerDtrPayroll::enabled())
    <br><strong>Paying from the attendance checker's DTR is on:</strong>
    full-time instructors, staff and utility workers are paid for the days and hours in their department's attendance register,
    so the hours in this table are their schedule only. Rates and deductions still come from here.
  @endif
</div>
@if($errors->has('attendance'))
  <div class="alert alert-danger m-3" role="alert">{{ $errors->first('attendance') }}</div>
@endif
