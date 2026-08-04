<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    /**
     * Show evaluation results (Admin only)
     */
    public function evaluationResults()
    {
        $responses = Evaluation::count();
        $avgUsability = Evaluation::avg('avg_usability');
        $avgEfficiency = Evaluation::avg('avg_efficiency');
        $avgSatisfaction = Evaluation::avg('avg_satisfaction');
        $overallAvg = Evaluation::avg('overall_avg');
        
        $roleData = Evaluation::selectRaw('respondent_role, COUNT(*) as count')
            ->groupBy('respondent_role')
            ->pluck('count', 'respondent_role')
            ->toArray();

        $trendData = Evaluation::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('count', 'date');

        $recentResponses = Evaluation::latest()->take(10)->get();

        return view('admin.evaluation-results', [
            'responses' => $responses,
            'avgUsability' => $avgUsability,
            'avgEfficiency' => $avgEfficiency,
            'avgSatisfaction' => $avgSatisfaction,
            'overallAvg' => $overallAvg,
            'roleData' => $roleData,
            'trendData' => $trendData,
            'recentResponses' => $recentResponses,
        ]);
    }

    /**
     * Store evaluation response (Admin form POST)
     */
    public function storeEvaluation(Request $request)
    {
        $userId = Auth::id();
        
        // Check if user already submitted
        $alreadySubmitted = Evaluation::where('user_id', $userId)->exists();
        if ($alreadySubmitted) {
            return redirect()
                ->back()
                ->with('eval_error', 'You have already submitted an evaluation.');
        }

        $validated = $request->validate([
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

        return redirect()
            ->route('admin.evaluation.results')
            ->with('eval_success', true);
    }
}

