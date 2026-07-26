# Tax & Government Deductions - Implementation Steps

## Step 1: Create Migration - `deduction_settings` table
- [x] Create migration for configurable deduction rates (withholding_tax, gsis, philhealth, pag_ibig, sss)

## Step 2: Create Migration - Add deduction columns to fulltime_timesheets
- [x] Add columns: withholding_tax, gsis, philhealth, pag_ibig, sss

## Step 3: Create DeductionSetting Model
- [x] Model with proper fillable fields and casts

## Step 4: Update FulltimeTimesheet Model
- [x] Add new deduction fields to $fillable

## Step 5: Create DeductionController
- [x] Index page with settings panel + computation table
- [x] Auto-computation logic
- [x] Monthly deduction summary
- [x] Update settings

## Step 6: Create Views
- [x] `admin/deductions/index.blade.php` - Main deductions page
- [x] `admin/deductions/summary.blade.php` - Monthly summary

## Step 7: Update Admin Dashboard Sidebar
- [x] Add "Tax & Government Deductions" navigation link

## Step 8: Add Routes
- [x] Register deduction routes in web.php

## Step 9: Update Payslip Logic
- [x] Update AdminController@sendPayslips to include govt deductions
- [x] Update payslip PDF view to display all deductions

## Step 10: Run Migrations & Test
- [ ] Run `php artisan migrate`
- [ ] Test deduction calculations

