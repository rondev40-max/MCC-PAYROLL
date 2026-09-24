<div class="alert alert-info m-3 d-print-none">
  <a href="{{ route('admin.attendance-payroll.index') }}" class="fw-bold">Attendance Payroll</a>
  shows recorded duty hours and payroll estimates. For enabled employees, Send Payslips uses those hours automatically; this table maintains scheduled hours, rates and deductions.
</div>
@if($errors->has('attendance'))
  <div class="alert alert-danger m-3" role="alert">{{ $errors->first('attendance') }}</div>
@endif
