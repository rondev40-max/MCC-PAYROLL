<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PayslipMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $employeeName;
    public string $designation;
    public string $department;
    public string $payPeriod;
    public string $totalDaysOrHours;
    public string $rate;
    public string $totalHonorarium;
    public $timesheet;
    public array $days;
    public array $holidays;

    public function __construct(
        string $employeeName,
        string $designation,
        string $department,
        string $payPeriod,
        string $totalDaysOrHours,
        string $rate,
        string $totalHonorarium,
        $timesheet,
        array $days,
        array $holidays
    ) {
        $this->employeeName = $employeeName;
        $this->designation = $designation;
        $this->department = $department;
        $this->payPeriod = $payPeriod;
        $this->totalDaysOrHours = $totalDaysOrHours;
        $this->rate = $rate;
        $this->totalHonorarium = $totalHonorarium;
        $this->timesheet = $timesheet;
        $this->days = $days;
        $this->holidays = $holidays;
    }

    public function build()
    {
        return $this->subject("Payslip - {$this->payPeriod}")
                    ->view('emails.payslip')
                    ->with([
                        'employeeName' => $this->employeeName,
                        'designation' => $this->designation,
                        'department' => $this->department,
                        'payPeriod' => $this->payPeriod,
                        'totalDaysOrHours' => $this->totalDaysOrHours,
                        'rate' => $this->rate,
                        'totalHonorarium' => $this->totalHonorarium,
                        'timesheet' => $this->timesheet,
                        'days' => $this->days,
                        'holidays' => $this->holidays,
                    ]);
    }
}