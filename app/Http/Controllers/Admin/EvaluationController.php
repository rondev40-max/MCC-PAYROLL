<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;

/**
 * Admin side of the evaluation is read-only. Admins see the aggregated
 * results; only employees fill in the form (App\Http\Controllers\EvaluationController).
 */
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
}
