<?php

namespace Vertex\Forms\Services;

use Vertex\Forms\Models\Form;
use Vertex\Forms\Models\FormAnalytic;
use Vertex\Forms\Models\FormSubmission;
use Illuminate\Support\Facades\DB;

class FormAnalyticsService
{
    /**
     * Get aggregated analytics for a form.
     */
    public function getAnalytics(Form $form, int $days = 30): array
    {
        $dateFrom = now()->subDays($days)->startOfDay();

        // Daily submissions
        $dailySubmissions = FormSubmission::select(
            DB::raw("DATE(created_at) as date"),
            DB::raw("COUNT(*) as count")
        )
        ->where("form_id", $form->id)
        ->where("created_at", ">=", $dateFrom)
        ->groupBy(DB::raw("DATE(created_at)"))
        ->orderBy("date")
        ->get()
        ->keyBy("date")
        ->map(fn ($row) => $row->count)
        ->toArray();

        // Total stats
        $totalSubmissions = $form->submissions()->count();
        $uniqueIPs = $form->submissions()->select("ip_address")->distinct()->count();
        
        // Views would be logged separately (FormViewLog model in future)
        $views = $totalSubmissions * 3; // placeholder until view logging implemented

        // Build daily time series
        $dates = [];
        $submissionsSeries = [];
        for ($d = $days - 1; $d >= 0; $d--) {
            $date = now()->subDays($d)->format("Y-m-d");
            $dates[] = $date;
            $submissionsSeries[] = $dailySubmissions[$date] ?? 0;
        }

        // Field completion rate (percent of submissions where field is non-empty)
        $fieldsCompletion = [];
        foreach ($form->fields as $field) {
            $filledCount = DB::table("form_submission_values")
                ->where("field_id", $field->id)
                ->whereNotNull("value")
                ->where("value", "!=", "")
                ->count();
            $completion = $totalSubmissions > 0 ? round(($filledCount / $totalSubmissions) * 100, 1) : 0;
            $fieldsCompletion[$field->label] = $completion;
        }

        // Average time to complete (future: track timestamps between first view and submission)
        $avgTime = 0;

        return [
            "total_submissions" => $totalSubmissions,
            "unique_visitors" => $uniqueIPs,
            "views" => $views,
            "conversion_rate" => $views > 0 ? round(($totalSubmissions / $views) * 100, 2) : 0,
            "daily" => [
                "dates" => $dates,
                "submissions" => $submissionsSeries,
            ],
            "fields_completion" => $fieldsCompletion,
            "avg_time_seconds" => $avgTime,
        ];
    }

    /**
     * Record a form view (call from middleware or view composer).
     */
    public function recordView(Form $form, string $ip, ?string $userAgent = null): void
    {
        $date = now()->toDateString();
        $analytic = FormAnalytic::firstOrNew(["form_id" => $form->id, "date" => $date]);
        $analytic->increment("views");

        // Unique visitor tracking (simplified)
        $hash = md5($ip . ($userAgent ?? ""));
        $key = "form_view_unique:{$form->id}:{$date}:" . substr($hash, 0, 8);
        if (!cache()->get($key)) {
            $analytic->increment("unique_visitors");
            cache()->put($key, true, 86400 * 7); // keep for a week
        }
    }

    /**
     * Recalculate analytics for all forms (cron job).
     */
    public function recalculate(Form $form): void
    {
        // Regenerate daily stats from submissions
        $submissions = $form->submissions()
            ->select(DB::raw("DATE(created_at) as date"), DB::raw("COUNT(*) as count"))
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderBy("date")
            ->get()
            ->keyBy("date");

        foreach ($submissions as $date => $row) {
            FormAnalytic::updateOrCreate(
                ["form_id" => $form->id, "date" => $date],
                ["submissions" => $row->count]
            );
        }
    }
}
