# Analytics

Form performance metrics and insights.

## Metrics

### Views

Number of times form page was loaded (unique per session/IP).

### Submissions

Total completed submissions.

### Conversion Rate

(submissions / views) * 100%

### Average Time to Complete

Average time between form load and submission (seconds).

### Abandonment Rate

1 - conversion_rate (estimated).

### Field Completion Rate

For each field: (non-empty submissions / total submissions) * 100%

### Device/Browser (coming soon)

Breakdown by desktop/mobile/tablet via User-Agent parsing.

## Data Collection

### View Logging (optional)

Middleware LogFormView (config: orms.log_form_views) increments:

- orm_analytics.views – total page views
- orm_analytics.unique_visitors – deduped by IP + daily
- Raw events optionally stored in orm_view_logs for deeper analysis

Deduplication: Same IP + form within 1 hour counts as 1 view.

### Submission Logging

Every successful submission automatically increments submissions count via FormAnalytic::incrementSubmissions().

### Daily Aggregation

Cron job (php artisan forms:recalculate-analytics) aggregates raw logs into orm_analytics table:

| Column | Description |
|--------|-------------|
| orm_id | FK to forms |
| date | YYYY-MM-DD |
| iews | daily views |
| unique_visitors | unique IPs |
| submissions | daily submissions |
| vg_time_seconds | avg completion time |
| 	op_fields | JSON array of most completed fields |

## API Endpoints

### Summary

GET /admin/forms/{form}/analytics

`json
{
  "analytics": {
    "totalViews": 1520,
    "totalSubmissions": 234,
    "conversionRate": 15.39,
    "avgTimeSeconds": 45.2,
    "todaySubmissions": 12,
    "thisWeekSubmissions": 87,
    "last7Days": [
      { "date": "2026-05-07", "views": 50, "submissions": 8 },
      { "date": "2026-05-08", "views": 62, "submissions": 10 },
      ...
    ]
  }
}
`

### Time-Series (for charts)

GET /admin/forms/{form}/analytics/data?days=30

`json
{
  "labels": ["May 1", "May 2", ...],
  "views": [45, 52, ...],
  "submissions": [8, 12, ...]
}
`

## Admin Dashboard Charts

- **Line chart**: Views vs Submissions (30 days)
- **Gauge**: Conversion rate
- **Bar chart**: Field completion % (top 10 fields)
- **Pie chart**: Device breakdown (future)

Library: Chart.js (lightweight, responsive).

## Privacy

- No personal data stored in analytics (IP hashed, no cookies)
- GDPR compliant: no tracking cookies
- Retention: configurable (orms.analytics_retention_days, default 90 days)
- Automatic cleanup via orms:cleanup-analytics command

## Export

Analytics can be exported as CSV:

POST /admin/forms/{form}/export-analytics?format=csv

Columns: date, views, unique_visitors, submissions, avg_time_seconds

## Filtering

Admin UI allows filtering by date range:
- Last 7 days
- Last 30 days
- Last 90 days
- Custom range

## Integration with Other Modules

- **Vertex Orders**: checkout form conversion tracking
- **Vertex Pages**: embed form analytics overlay on page
- **Vertex Search**: search submissions across forms

## Best Practices


Submission retention is handled separately by `php artisan
forms:cleanup-submissions`. It uses each form's `retention_days` setting or the
global `FORMS_SUBMISSION_RETENTION_DAYS` fallback and deletes associated private
uploads before removing expired submissions. The command is scheduled daily.
1. Wait for at least 100 views before drawing conclusions
2. A/B test form layouts to improve conversion
3. Monitor abandonment rate – high rate indicates complex form
4. Check field completion: low % → consider making optional or conditional
5. Set up email alerts for spikes in submissions (future)

## Troubleshooting

**Analytics show 0 views**
- Ensure orms.log_form_views enabled
- Check middleware LogFormView is active (in Kernel)
- Verify orm_analytics row created for today

**Conversion rate looks wrong**
- Views count unique per IP/day, not page loads
- Submissions count only successful (completed) ones

**Charts not rendering**
- Check Chart.js loaded (via CDN or asset)
- Verify JSON response format (date keys)
- Console errors → check API route permissions

## Scheduled Commands

`ash
# Recalculate daily aggregates (runs at midnight)
php artisan forms:recalculate-analytics

# Cleanup old analytics (keep 90 days)
php artisan forms:cleanup-analytics

# Export monthly report
php artisan forms:export-monthly-report --month=05 --year=2026
`

## Future Enhancements

- Funnel analysis (step-by-step drop-off)
- UTM parameter tracking
- Heatmaps (mouse movement on form)
- Session recordings (with consent)
- Real-time submission alerts
- Export to Google Sheets
- Benchmark against industry averages
