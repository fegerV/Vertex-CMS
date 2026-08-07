<?php

namespace Vertex\Forms\Services;

use App\System\Services\EmailService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Factory as ValidatorFactory;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Throwable;
use Vertex\Forms\Models\Form;
use Vertex\Forms\Models\FormField;
use Vertex\Forms\Models\FormSubmission;
use Vertex\Forms\Models\FormSubmissionValue;
use Vertex\Forms\Models\FormVersion;

class FormService
{
    public function __construct(
        private readonly EmailService $emailService,
        private readonly ValidatorFactory $validator,
        private readonly FormCalculatorEngine $calculatorEngine,
        private readonly FormConditionEngine $conditionEngine,
        private readonly FormSpamProtectionService $spamProtection,
    ) {}

    /**
     * Render form fields as array for frontend (Inertia/Vue) with pagination and conditions.
     */
    public function renderForm(Form $form, array $old = [], int $currentPage = 1): array
    {
        $allFields = $form->fields()
            ->where('visible', true)
            ->orderBy('sort_order')
            ->get();

        // Split into pages
        $pages = [];
        $currentPageFields = collect();
        $pageNum = 1;

        foreach ($allFields as $field) {
            if ($field->type === 'page_break') {
                $pages[$pageNum] = $currentPageFields;
                $pageNum++;
                $currentPageFields = collect();

                continue;
            }
            $currentPageFields->push($field);
        }
        $pages[$pageNum] = $currentPageFields;

        $totalPages = count($pages);
        $currentPage = max(1, min($currentPage, $totalPages));
        $pageFields = $pages[$currentPage] ?? collect();

        // Build rendered field configs
        $rendered = [];
        foreach ($pageFields as $field) {
            $fieldConfig = [
                'id' => $field->id,
                'name' => $field->name,
                'label' => $field->label,
                'type' => $field->type,
                'required' => $field->required,
                'placeholder' => $field->placeholder,
                'help_text' => $field->help_text,
                'default_value' => $old[$field->name] ?? $field->default_value ?? null,
                'options' => $this->parseOptions($field),
                'css_class' => $field->css_class,
                'validation' => $this->buildValidationRules($field),
                'conditional' => $field->options['conditional'] ?? null,
                'width' => $field->options['width'] ?? 'full',
                'column_width' => $field->options['column_width'] ?? 12,
            ];

            // Calculator-specific
            if ($field->type === 'calculator') {
                $fieldConfig['calculator'] = [
                    'formula' => $field->options['formula'] ?? '',
                    'depends_on' => $field->options['depends_on'] ?? [],
                    'prefix' => $field->options['prefix'] ?? '',
                    'suffix' => $field->options['suffix'] ?? '',
                    'precision' => $field->options['precision'] ?? 2,
                    'live' => $field->options['live'] ?? true,
                    'readonly' => $field->options['readonly'] ?? true,
                ];
            }

            // File-specific
            if ($field->type === 'file') {
                $fieldConfig['max_size'] = $field->options['max_size'] ?? null;
                $fieldConfig['mime_types'] = $field->options['mime_types'] ?? null;
                $fieldConfig['multiple'] = $field->options['multiple'] ?? false;
            }

            // Date/datetime
            if (in_array($field->type, ['date', 'time', 'datetime-local'])) {
                $fieldConfig['min'] = $field->options['min'] ?? null;
                $fieldConfig['max'] = $field->options['max'] ?? null;
            }

            $rendered[] = $fieldConfig;
        }

        return [
            'fields' => $rendered,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'show_progress' => $form->settings['show_progress'] ?? true,
            'show_page_titles' => $form->settings['show_page_titles'] ?? false,
        ];
    }

    /**
     * Create snapshot of current form state (for versioning).
     */
    public function createSnapshot(Form $form, ?string $comment = null, ?int $userId = null): FormVersion
    {
        $contentJson = [
            'name' => $form->name,
            'type' => $form->type,
            'description' => $form->description,
            'settings' => $form->settings,
            'fields' => $form->fields()
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($f) => [
                    'name' => $f->name,
                    'label' => $f->label,
                    'type' => $f->type,
                    'sort_order' => $f->sort_order,
                    'required' => $f->required,
                    'visible' => $f->visible,
                    'options' => $f->options,
                    'default_value' => $f->default_value,
                    'placeholder' => $f->placeholder,
                    'help_text' => $f->help_text,
                    'css_class' => $f->css_class,
                ])
                ->values(),
        ];

        $lastVersion = $form->versions()->orderBy('version_number', 'desc')->first();
        $nextNumber = ($lastVersion?->version_number ?? 0) + 1;

        $version = FormVersion::create([
            'form_id' => $form->id,
            'version_number' => $nextNumber,
            'content_json' => $contentJson,
            'user_id' => $userId,
            'comment' => $comment,
        ]);

        $maxSnapshots = max(1, (int) config('forms.max_snapshots_per_form', 50));
        $staleVersionIds = $form->versions()
            ->orderByDesc('version_number')
            ->skip($maxSnapshots)
            ->take(PHP_INT_MAX)
            ->pluck('id');

        if ($staleVersionIds->isNotEmpty()) {
            FormVersion::query()->whereKey($staleVersionIds)->delete();
        }

        return $version;
    }

    /**
     * Restore form to a specific version.
     */
    public function restoreVersion(Form $form, FormVersion $version, ?int $userId = null): void
    {
        $content = $version->content_json;

        \DB::transaction(function () use ($form, $content, $userId) {
            $form->update([
                'name' => $content['name'],
                'type' => $content['type'] ?? $form->type,
                'description' => $content['description'] ?? null,
                'settings' => $content['settings'] ?? [],
            ]);

            $form->fields()->delete();
            foreach ($content['fields'] ?? [] as $fieldData) {
                $form->fields()->create($fieldData);
            }

            // Create new snapshot for restore action
            $this->createSnapshot($form, "Restored to version {$version->version_number}", $userId);
        });
    }

    /**
     * Validate form submission with conditional logic.
     */
    public function validate(Form $form, Request $request): Validator
    {
        $data = $request->all();
        $visibleFields = $this->conditionEngine->evaluateFields(
            $form->fields->all(),
            $data
        );

        $rules = [];
        $messages = [];
        $attributes = [];

        foreach ($form->fields as $field) {
            if (! $field->visible) {
                continue;
            }
            if (! in_array($field->name, $visibleFields, true)) {
                continue;
            }

            if ($field->type === 'file' && ($field->options['multiple'] ?? false)) {
                $rules[$field->name] = $field->required ? 'required|array|min:1' : 'nullable|array';
                $rules[$field->name.'.*'] = $this->buildFileValidationRules($field);
                $attributes[$field->name] = $field->label;

                continue;
            }

            $fieldRules = $this->buildValidationRules($field);
            if ($fieldRules) {
                $rules[$field->name] = $fieldRules;
            }
            if ($field->required && ! isset($rules[$field->name])) {
                $rules[$field->name] = 'required';
            }
            $attributes[$field->name] = $field->label;
        }

        // Honeypot & reCAPTCHA
        $settings = $form->settings ?? [];
        if ($settings['honeypot_enabled'] ?? config('forms.honeypot_enabled', true)) {
            $honeypotName = 'form_'.md5($form->slug.'_hp');
            $rules[$honeypotName] = 'prohibited';
            $messages[$honeypotName.'.prohibited'] = __('forms.validation_honeypot_spam');
        }

        if ($settings['recaptcha_enabled'] ?? config('forms.recaptcha_enabled', false)) {
            $version = $settings['recaptcha_version'] ?? config('forms.recaptcha_version', 'v2');
            if ($version === 'v2') {
                $rules['g-recaptcha-response'] = 'required|string';
            } else {
                $rules['recaptcha_token'] = 'required|string';
            }
        }

        if ($settings['turnstile_enabled'] ?? config('forms.turnstile_enabled', false)) {
            $rules['cf-turnstile-response'] = 'required|string';
        }

        return $this->validator->make(
            array_merge($request->all(), ['submission_id' => $request->input('submission_id')]),
            $rules,
            $messages,
            $attributes
        );
    }

    /**
     * Process form submission.
     */
    public function submit(Form $form, Request $request): FormSubmission
    {
        $validator = $this->validate($form, $request);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->spamProtection->verify($form, $request);
        $idempotencyKey = $this->idempotencyKey($request);
        if ($idempotencyKey !== null) {
            $existingSubmission = $form->submissions()
                ->where('idempotency_key', $idempotencyKey)
                ->with('values.field')
                ->first();

            if ($existingSubmission !== null) {
                return $existingSubmission;
            }
        }

        $this->checkLimits($form, $request);
        $total = $this->calculateTotal($form, $request->all());
        $visibleFieldNames = $this->conditionEngine->evaluateFields($form->fields->all(), $request->all());

        $storedFiles = [];

        try {
            $submission = DB::transaction(function () use ($form, $request, $total, $visibleFieldNames, $idempotencyKey, &$storedFiles): FormSubmission {
                $submission = FormSubmission::create([
                    'form_id' => $form->id,
                    'submission_id' => Str::uuid()->toString(),
                    'idempotency_key' => $idempotencyKey,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'user_id' => $request->user()?->id,
                    'status' => 'completed',
                    'meta' => ['total' => $total],
                ]);

                foreach ($form->fields as $field) {
                    if (! $field->visible || ! in_array($field->name, $visibleFieldNames, true)) {
                        continue;
                    }

                    $value = $request->input($field->name);
                    if (is_array($value)) {
                        $value = array_values($value);
                    }

                    if ($field->type === 'file' && $request->hasFile($field->name)) {
                        $files = Arr::wrap($request->file($field->name));
                        $value = [];

                        foreach ($files as $file) {
                            $disk = config('forms.upload_disk', 'local');
                            $directory = trim(config('forms.upload_dir', 'form-uploads'), '/')."/{$form->slug}";
                            $path = $file->store($directory, $disk);
                            $storedFiles[] = [$disk, $path];
                            $value[] = [
                                'disk' => $disk,
                                'path' => $path,
                                'name' => $file->getClientOriginalName(),
                                'size' => $file->getSize(),
                                'mime' => $file->getMimeType(),
                            ];
                        }

                        if (! ($field->options['multiple'] ?? false)) {
                            $value = $value[0] ?? null;
                        }
                    }

                    FormSubmissionValue::create([
                        'submission_id' => $submission->id,
                        'field_id' => $field->id,
                        'value' => $value,
                    ]);
                }

                return $submission->load('values.field');
            });
        } catch (Throwable $exception) {
            foreach ($storedFiles as [$disk, $path]) {
                Storage::disk($disk)->delete($path);
            }

            if ($exception instanceof UniqueConstraintViolationException && $idempotencyKey !== null) {
                $existingSubmission = $form->submissions()
                    ->where('idempotency_key', $idempotencyKey)
                    ->with('values.field')
                    ->first();

                if ($existingSubmission !== null) {
                    return $existingSubmission;
                }
            }

            throw $exception;
        }

        try {
            $this->sendNotifications($form, $submission);
        } catch (Throwable $exception) {
            Log::error('Form notification failed', [
                'form_id' => $form->id,
                'submission_id' => $submission->submission_id,
                'error' => $exception->getMessage(),
            ]);
        }

        return $submission;
    }

    private function idempotencyKey(Request $request): ?string
    {
        $providedKey = trim((string) ($request->header('Idempotency-Key') ?: $request->input('idempotency_key', '')));

        return $providedKey === '' ? null : hash('sha256', $providedKey);
    }

    /**
     * Calculate total using FormCalculatorEngine.
     */
    private function calculateTotal(Form $form, array $data): float
    {
        $total = 0.0;

        foreach ($form->fields as $field) {
            if ($field->type !== 'calculator') {
                continue;
            }

            $formula = $field->options['formula'] ?? '';
            if (! $formula) {
                continue;
            }

            $dependsOn = $field->options['depends_on'] ?? [];
            $values = array_intersect_key($data, array_flip($dependsOn));

            $result = $this->calculatorEngine->evaluate($formula, $values);
            $precision = $field->options['precision'] ?? 2;
            $result = round($result, $precision);

            $total += $result;
        }

        // Tax
        if (($form->settings['tax_enabled'] ?? false) && isset($form->settings['tax_rate'])) {
            $taxRate = (float) $form->settings['tax_rate'];
            $total = $total * (1 + $taxRate / 100);
        }

        // Discount

        return max(0, round($total, 2));
    }

    /**
     * Build validation rules.
     */
    private function buildValidationRules(FormField $field): string
    {
        $rules = [];

        if ($field->required) {
            $rules[] = 'required';
        }

        switch ($field->type) {
            case 'email':
                $rules[] = 'email';
                break;
            case 'url':
                $rules[] = 'url';
                break;
            case 'number':
                $rules[] = 'numeric';
                if (isset($field->options['min'])) {
                    $rules[] = 'min:'.$field->options['min'];
                }
                if (isset($field->options['max'])) {
                    $rules[] = 'max:'.$field->options['max'];
                }
                break;
            case 'rating':
                $rules[] = 'integer';
                $rules[] = 'min:1';
                $rules[] = 'max:'.(int) ($field->options['scale'] ?? 5);
                break;
            case 'tel':
                $rules[] = 'regex:/^[\\d\\+\\-\\(\\) ]+$/';
                break;
            case 'consent':
                $rules[] = 'accepted';
                break;
            case 'name':
            case 'address':
                $rules[] = 'array';
                break;
            case 'file':
                $rules = array_merge($rules, explode('|', $this->buildFileValidationRules($field)));
                break;
            case 'textarea':
            case 'text':
                if (isset($field->options['minlength'])) {
                    $rules[] = 'min:'.$field->options['minlength'];
                }
                if (isset($field->options['maxlength'])) {
                    $rules[] = 'max:'.$field->options['maxlength'];
                }
                if (isset($field->options['pattern'])) {
                    $rules[] = 'regex:'.str_replace(['/', '|'], '-', $field->options['pattern']);
                }
                break;
        }

        return implode('|', $rules);
    }

    private function buildFileValidationRules(FormField $field): string
    {
        $rules = ['file'];
        $maxKilobytes = (int) ($field->options['max_size'] ?? ceil(config('forms.max_file_size', 5242880) / 1024));
        $rules[] = 'max:'.max(1, $maxKilobytes);
        $mimeTypes = $field->options['mime_types'] ?? config('forms.allowed_mime_types', []);

        if ($mimeTypes) {
            $rules[] = 'mimetypes:'.implode(',', array_map('trim', Arr::wrap($mimeTypes)));
        }

        return implode('|', $rules);
    }

    /**
     * Parse field options for frontend.
     */
    private function parseOptions(FormField $field): array
    {
        $opts = $field->options ?? [];

        if (in_array($field->type, ['select', 'radio', 'checkbox_group']) && isset($opts['choices'])) {
            $choices = [];
            foreach ($opts['choices'] as $value => $label) {
                if (is_array($label)) {
                    $choices[] = [
                        'value' => (string) ($label['value'] ?? $value),
                        'label' => (string) ($label['label'] ?? $label['value'] ?? $value),
                    ];

                    continue;
                }

                $choices[] = ['value' => (string) $value, 'label' => (string) $label];
            }
            $opts['choices'] = $choices;
        }

        return $opts;
    }

    /**
     * Check entry limits.
     * Column `forms.daily_limit` is the per-form hard cap.
     * Form setting `daily_limit_per_ip` in `forms.settings` overrides per-IP soft cap.
     * Global fallbacks: `forms.daily_limit_per_ip_global`, `forms.max_entries_global`.
     */
    private function checkLimits(Form $form, Request $request): void
    {
        $settings = $form->settings ?? [];

        $rateLimit = max(1, (int) ($settings['max_submissions_per_minute'] ?? config('forms.max_submissions_per_minute', 10)));
        $rateKey = 'forms:submit:'.$form->id.':'.hash('sha256', (string) $request->ip());

        if (RateLimiter::tooManyAttempts($rateKey, $rateLimit)) {
            abort(429, __('forms.error_rate_limit'), [
                'Retry-After' => RateLimiter::availableIn($rateKey),
            ]);
        }

        RateLimiter::hit($rateKey, 60);

        $dailyLimit = $settings['daily_limit_per_ip'] ?? config('forms.daily_limit_per_ip_global', null);
        if ($dailyLimit) {
            $count = FormSubmission::where('form_id', $form->id)
                ->where('ip_address', $request->ip())
                ->whereDate('created_at', now()->toDateString())
                ->count();

            if ($count >= $dailyLimit) {
                throw new \Exception(__('forms.error_daily_limit_reached'));
            }
        }

        $maxEntries = $settings['max_entries'] ?? config('forms.max_entries_global', null);
        if ($maxEntries) {
            $total = FormSubmission::where('form_id', $form->id)->count();
            if ($total >= $maxEntries) {
                throw new \Exception(__('forms.error_form_closed'));
            }
        }

        $now = now();
        $from = $form->available_from;
        $to = $form->available_to;

        if ($from && $now->lt($from)) {
            throw new \Exception(__('forms.error_form_not_open'));
        }

        if ($to && $now->gt($to)) {
            throw new \Exception(__('forms.error_form_closed_date'));
        }
    }

    /**
     * Send email notifications.
     */
    private function sendNotifications(Form $form, FormSubmission $submission): void
    {
        $settings = $form->settings ?? [];
        $globalSettings = [
            'notify_admin' => config('forms.notify_admin', true),
            'admin_emails' => config('forms.notify_admin_emails', []),
        ];
        $settings = array_merge($globalSettings, $settings);

        if ($settings['notify_admin'] ?? false) {
            $emails = is_array($settings['admin_emails'] ?? null)
                ? $settings['admin_emails']
                : explode("\n", trim((string) ($settings['admin_emails'] ?? '')));

            foreach (array_filter($emails) as $email) {
                $this->emailService->send(
                    'form_submission',
                    trim($email),
                    'Admin',
                    [
                        'form_name' => $form->name,
                        'submission_id' => $submission->submission_id,
                        'fields' => $submission->values->mapWithKeys(fn ($v) => [$v->field->name => $v->value])->all(),
                        'site_name' => config('site.name', 'VertexCMS'),
                    ]
                );
            }
        }

        if ($settings['notify_user'] ?? false) {
            $userEmail = $submission->values->firstWhere('field.name', 'email')?->value;
            if ($userEmail && filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
                $this->emailService->send(
                    $settings['user_email_template'] ?? 'form_confirmation',
                    $userEmail,
                    'User',
                    [
                        'form_name' => $form->name,
                        'submission_id' => $submission->submission_id,
                        'site_name' => config('site.name', 'VertexCMS'),
                        'custom_message' => $settings['autoresponder_body'] ?? '',
                    ]
                );
            }
        }
    }

    /**
     * Get submissions stats.
     */
    public function getStats(Form $form): array
    {
        $total = $form->submissions()->count();
        $today = $form->submissions()->whereDate('created_at', now()->toDateString())->count();
        $spam = $form->submissions()->where('status', 'spam')->count();
        $last = $form->submissions()->latest()->first()?->created_at;

        return compact('total', 'today', 'spam', 'last');
    }
}
