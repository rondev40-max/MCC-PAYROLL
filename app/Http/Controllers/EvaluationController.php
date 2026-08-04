<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evaluation;
use Illuminate\Support\Facades\Auth;

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
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted an evaluation.'
            ], 422);
        }

        $validated = $request->validate([
            // Employee portal form fields
            'respondent_role' => 'required|string',

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
            'avg_usability' => 'required|numeric',
            'avg_efficiency' => 'required|numeric',
            'avg_satisfaction' => 'required|numeric',
            'overall_avg' => 'required|numeric',
            'feedback_useful' => 'nullable|string',
            'feedback_problems' => 'nullable|string',
            'feedback_suggestions' => 'nullable|string',
        ]);

        // Store evaluation
        Evaluation::create([
            'user_id' => $userId,
            'respondent_role' => $validated['respondent_role'],
            'usability_scores' => json_encode([
                'q1' => $validated['usability_1'],
                'q2' => $validated['usability_2'],
                'q3' => $validated['usability_3'],
                'q4' => $validated['usability_4'],
                'q5' => $validated['usability_5'],
            ]),
            'efficiency_scores' => json_encode([
                'q1' => $validated['eff_1'],
                'q2' => $validated['eff_2'],
                'q3' => $validated['eff_3'],
                'q4' => $validated['eff_4'],
                'q5' => $validated['eff_5'],
            ]),
            'satisfaction_scores' => json_encode([
                'q1' => $validated['sat_1'],
                'q2' => $validated['sat_2'],
                'q3' => $validated['sat_3'],
                'q4' => $validated['sat_4'],
                'q5' => $validated['sat_5'],
            ]),
            'feedback' => json_encode([
                'useful' => $validated['feedback_useful'],
                'problems' => $validated['feedback_problems'],
                'suggestions' => $validated['feedback_suggestions'],
            ]),
            'avg_usability' => $validated['avg_usability'],
            'avg_efficiency' => $validated['avg_efficiency'],
            'avg_satisfaction' => $validated['avg_satisfaction'],
            'overall_avg' => $validated['overall_avg'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Evaluation submitted successfully'
        ]);
    }
}