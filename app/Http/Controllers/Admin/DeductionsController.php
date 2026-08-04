<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeductionSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeductionsController extends Controller
{
    /**
     * Display the tax & government deductions settings page.
     */
    public function index(): View
    {
        $settings = DeductionSetting::orderByRaw(
            "CASE deduction_type
                WHEN 'withholding_tax' THEN 1
                WHEN 'gsis' THEN 2
                WHEN 'philhealth' THEN 3
                WHEN 'pag_ibig' THEN 4
                WHEN 'sss' THEN 5
                ELSE 6
            END"
        )->get();

        return view('admin.deductions.index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update a single deduction setting.
     */
    public function update(Request $request, DeductionSetting $setting): RedirectResponse
    {
        $data = $request->validate([
            'rate_type'   => 'required|in:percentage,fixed',
            'rate_value'  => 'required|numeric|min:0',
            'min_amount'  => 'nullable|numeric|min:0',
            'max_amount'  => 'nullable|numeric|min:0|gte:min_amount',
            'is_active'   => 'nullable|boolean',
            'description' => 'nullable|string|max:1000',
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $setting->update($data);

        return back()->with('success', ucfirst(str_replace('_', ' ', $setting->deduction_type)).' deduction updated.');
    }

    /**
     * Toggle a deduction setting's active state.
     */
    public function toggle(DeductionSetting $setting): RedirectResponse
    {
        $setting->update(['is_active' => ! $setting->is_active]);

        return back()->with('success', ucfirst(str_replace('_', ' ', $setting->deduction_type)).' is now '.($setting->is_active ? 'active' : 'inactive').'.');
    }
}