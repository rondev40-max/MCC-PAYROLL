<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evaluation;
use Illuminate\Support\Facades\Auth;

/**
 * Employee portal evaluation. This is the only place an evaluation can be
 * submitted — the routes sit behind role:employee. Admins only see the
 * aggregated results (Admin\EvaluationController).
 */
class EvaluationController extends Controller
{
    /**
     * Employee portal: show evaluation form (one submission per account)
     */
    public function showEmployeeForm(Request $request)
    {

        $alreadySubmitted = Evaluation::where('user_id', Auth::id())->exists();

        // $employee is used by the blade for sidebar avatar/name.
        // In this project, employee portal is driven by Employee model.
        $employee = Auth::user();

        return view('employee.employee_evaluation', [
            'employee' => $employee,
            'alreadySubmitted' => $alreadySubmitted,
        ]);
    }

    /**
     * Employee portal: store evaluation response
     */
    public function storeEvaluation(Request $request)
    {
        $userId = Auth::id();

        // Check if user already submitted
        $alreadySubmitted = Evaluation::where('user_id', $userId)->exists();
        if ($alreadySubmitted) {
            return redirect()
                ->route('employee.evaluation.form')
                ->with('eval_error', 'You have already submitted an evaluation.');
        }

        $validated = $request->validate([
            // Employee portal form fields
            'respondent_role' => 'required|string|in:Administrator,Faculty,Staff,Other',

            'usability_1' => 'required|numeric|between:1,5',
            'usability_2' => 'required|numeric|between:1,5',
            'usability_3' => 'required|numeric|between:1,5',
            'usability_4' => 'required|numeric|between:1,5',
            'usability_5' => 'required|numeric|between:1,5',
            'eff_1' => 'required|numeric|between:1,5',
            'eff_2' => 'required|numeric|between:1,5',
            'eff_3' => 'required|numeric|between:1,5',
            'eff_4' => 'required|numeric|between:1,5',
            'eff_5' => 'required|numeric|between:1,5',
            'sat_1' => 'required|numeric|between:1,5',
            'sat_2' => 'required|numeric|between:1,5',
            'sat_3' => 'required|numeric|between:1,5',
            'sat_4' => 'required|numeric|between:1,5',
            'sat_5' => 'required|numeric|between:1,5',
            'feedback_useful' => 'nullable|string',
            'feedback_problems' => 'nullable|string',
            'feedback_suggestions' => 'nullable|string',
        ]);

        $usability = [
            'q1' => $validated['usability_1'],
            'q2' => $validated['usability_2'],
            'q3' => $validated['usability_3'],
            'q4' => $validated['usability_4'],
            'q5' => $validated['usability_5'],
        ];
        $efficiency = [
            'q1' => $validated['eff_1'],
            'q2' => $validated['eff_2'],
            'q3' => $validated['eff_3'],
            'q4' => $validated['eff_4'],
            'q5' => $validated['eff_5'],
        ];
        $satisfaction = [
            'q1' => $validated['sat_1'],
            'q2' => $validated['sat_2'],
            'q3' => $validated['sat_3'],
            'q4' => $validated['sat_4'],
            'q5' => $validated['sat_5'],
        ];

        // Averages are computed here, not taken from the form's hidden fields,
        // so the admin results can't be skewed by an edited request.
        $avgUsability = array_sum($usability) / count($usability);
        $avgEfficiency = array_sum($efficiency) / count($efficiency);
        $avgSatisfaction = array_sum($satisfaction) / count($satisfaction);
        $overallAvg = ($avgUsability + $avgEfficiency + $avgSatisfaction) / 3;

        // The score/feedback columns are cast to array on the model, so pass
        // arrays — json_encode() here would store them double-encoded.
        Evaluation::create([
            'user_id' => $userId,
            'respondent_role' => $validated['respondent_role'],
            'usability_scores' => $usability,
            'efficiency_scores' => $efficiency,
            'satisfaction_scores' => $satisfaction,
            'feedback' => [
                'useful' => $validated['feedback_useful'] ?? null,
                'problems' => $validated['feedback_problems'] ?? null,
                'suggestions' => $validated['feedback_suggestions'] ?? null,
            ],
            'avg_usability' => round($avgUsability, 2),
            'avg_efficiency' => round($avgEfficiency, 2),
            'avg_satisfaction' => round($avgSatisfaction, 2),
            'overall_avg' => round($overallAvg, 2),
        ]);

        // The form is a plain POST (not fetch), so redirect back to it; the page
        // then shows the "already submitted" state plus the eval_success alert.
        return redirect()
            ->route('employee.evaluation.form')
            ->with('eval_success', true);
    }
}
