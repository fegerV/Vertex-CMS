@php
    $formId = $form->id ?? null;
    $uniqueId = $uniqueId ?? 'form_'.($formId ?? Str::random());
    $actionUrl = $actionUrl ?? '#';
    $nonce = $nonce ?? csrf_token();
    $currentPage = $formConfig['current_page'] ?? 1;
    $totalPages = $formConfig['total_pages'] ?? 1;
    $showProgress = $formConfig['show_progress'] ?? true;
    $showPageTitles = $formConfig['show_page_titles'] ?? false;
    $fields = $formConfig['fields'] ?? [];
    $formTitle = $settings['title'] ?? ($form->name ?? '');
    $formDescription = $settings['description'] ?? ($form->description ?? '');
    $buttonText = $settings['button_text'] ?? ($form->settings['submit_label'] ?? __('forms.submit'));
    $successMessage = $settings['success_message'] ?? ($form->settings['success_message'] ?? __('forms.success_title'));
    $globalTheme = config_value('forms.default_theme', config('forms.default_theme', 'default'));
    $localTheme = $form->settings['theme'] ?? ($settings['theme'] ?? 'inherit');
    $theme = $localTheme && $localTheme !== 'inherit' ? $localTheme : $globalTheme;
    $theme = preg_replace('/[^a-z0-9_-]/i', '', (string) ($theme ?: 'default')) ?: 'default';
    $customCss = trim((string) ($form->settings['custom_css'] ?? config_value('forms.custom_css', '')));
    $pageOfText = __('forms.page_of', ['page' => ':page', 'total' => ':total']);
    $prevText = __('forms.prev');
    $nextText = __('forms.next');
    $honeypotEnabled = $form->settings['honeypot_enabled'] ?? config('forms.honeypot_enabled', true);
    $honeypotName = 'form_'.md5($form->slug.'_hp');
    $recaptchaEnabled = $form->settings['recaptcha_enabled'] ?? config('forms.recaptcha_enabled', false);
    $recaptchaVersion = $form->settings['recaptcha_version'] ?? config('forms.recaptcha_version', 'v2');
    $recaptchaSiteKey = config('forms.recaptcha_site_key');
    $turnstileEnabled = $form->settings['turnstile_enabled'] ?? config('forms.turnstile_enabled', false);
    $turnstileSiteKey = config('forms.turnstile_site_key');
@endphp

<div
    id="{{ $uniqueId }}"
    class="vc-form-wrapper vc-form-{{ $theme }}"
    data-form-id="{{ $formId }}"
    data-action-url="{{ $actionUrl }}"
    data-current-page="{{ $currentPage }}"
    data-total-pages="{{ $totalPages }}"
    x-data="formBuilder(@json($formConfig), '{{ $uniqueId }}')"
>
    @if($customCss !== '')
        <style>{!! $customCss !!}</style>
    @endif
    @if($formTitle)
        <h3 class="vc-form-title text-xl font-bold mb-2">{{ $formTitle }}</h3>
    @endif

    @if($formDescription)
        <p class="vc-form-description text-gray-600 mb-6">{{ $formDescription }}</p>
    @endif

    <div x-show="submitted" class="vc-form-success p-4 bg-green-50 text-green-700 rounded-md mb-4" x-html="successMessage"></div>

    <div x-show="!submitted">
        @if($showProgress && $totalPages > 1)
            <div class="vc-form-progress mb-6">
                <div class="flex items-center justify-between relative">
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 h-0.5 bg-gray-200 w-full"></div>
                    <template x-for="page in totalPages" :key="page">
                        <div class="vc-progress-step relative flex items-center justify-center w-8 h-8 rounded-full text-xs font-medium transition-colors"
                             :class="page <= currentPage ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'">
                            <span x-text="page"></span>
                        </div>
                    </template>
                </div>
            </div>
        @endif

        <form
            x-ref="formElement"
            action="{{ $actionUrl }}"
            method="POST"
            @submit.prevent="submitForm()"
            class="vc-form space-y-4"
            enctype="multipart/form-data"
        >
            <input type="hidden" name="_token" value="{{ $nonce }}">
            @if($honeypotEnabled)
                <div class="absolute -left-[10000px] h-px w-px overflow-hidden" aria-hidden="true">
                    <label for="{{ $uniqueId }}_hp">Leave this field empty</label>
                    <input id="{{ $uniqueId }}_hp" type="text" name="{{ $honeypotName }}" value="" tabindex="-1" autocomplete="off">
                </div>
            @endif
            <input type="hidden" name="idempotency_key" :value="idempotencyKey">

            @if($recaptchaEnabled && $recaptchaVersion === 'v2' && $recaptchaSiteKey)
                <div class="g-recaptcha" data-sitekey="{{ $recaptchaSiteKey }}"></div>
            @elseif($recaptchaEnabled && $recaptchaVersion === 'v3' && $recaptchaSiteKey)
                <input x-ref="recaptchaToken" type="hidden" name="recaptcha_token" value="">
            @endif

            @if($turnstileEnabled && $turnstileSiteKey)
                <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey }}"></div>
            @endif

            @foreach($fields as $field)
                <div
                    data-field-name="{{ $field['name'] }}"
                    class="vc-form-field-wrapper"
                    :class="'vc-field-'+'{{ $field['type'] }}'"
                    x-show="isFieldVisible('{{ $field['name'] }}')"
                    x-transition
                >
                     @include('forms::blocks._form-field', ['field' => $field])
                </div>
            @endforeach

            @if($totalPages > 1)
                <div class="vc-form-pagination flex justify-between items-center mt-6 pt-4 border-t">
                    <button
                        type="button"
                        @click="prevPage()"
                        x-show="currentPage > 1"
                        class="px-4 py-2 border rounded-md hover:bg-gray-50"
                    >
                        {{ $prevText }}
                    </button>
                    <span class="text-sm text-gray-500"
                          x-text="`{{ str_replace(':page', '${currentPage}', str_replace(':total', '${totalPages}', $pageOfText)) }}`"></span>
                    <button
                        type="button"
                        @click="nextPage()"
                        x-show="currentPage < totalPages"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        {{ $nextText }}
                    </button>
                    <button
                        type="submit"
                        x-show="currentPage === totalPages"
                        :disabled="loading"
                        class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 disabled:opacity-50"
                    >
                        <span x-text="buttonText"></span>
                        <svg x-show="loading" class="inline-block animate-spin h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            @else
                <button
                    type="submit"
                    :disabled="loading"
                    class="w-full px-6 py-3 bg-blue-600 text-white font-bold rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50"
                >
                    <span x-text="buttonText"></span>
                    <svg x-show="loading" class="inline-block animate-spin h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            @endif
        </form>

        <div x-show="errors.general" class="mt-4 p-3 bg-red-50 text-red-700 rounded-md" x-text="errors.general"></div>
    </div>
</div>

@if($recaptchaEnabled && $recaptchaSiteKey)
    <script src="https://www.google.com/recaptcha/api.js{{ $recaptchaVersion === 'v3' ? '?render='.urlencode($recaptchaSiteKey) : '' }}" async defer></script>
@endif
@if($turnstileEnabled && $turnstileSiteKey)
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('formBuilder', (formConfig, uniqueId) => ({
        currentPage: formConfig.current_page || 1,
        totalPages: formConfig.total_pages || 1,
        fields: formConfig.fields || [],
        showProgress: formConfig.show_progress !== false,
        buttonText: {{ Illuminate\Support\Js::from($buttonText) }},
        successMessage: {{ Illuminate\Support\Js::from($successMessage) }},
        loading: false,
        submitted: false,
        formData: {},
        errors: {},
        idempotencyKey: window.crypto?.randomUUID?.() || `${Date.now()}-${Math.random()}`,

        init() {
            this.fields.forEach(field => {
                if (field.default_value !== null && field.default_value !== undefined) {
                    this.$set(this.formData, field.name, field.default_value);
                } else if (['name', 'address'].includes(field.type)) {
                    this.$set(this.formData, field.name, {});
                } else if (field.type === 'checkbox_group') {
                    this.$set(this.formData, field.name, []);
                } else {
                    this.$set(this.formData, field.name, '');
                }
            });

            this.setupCalculators();
            this.setupConditionals();
        },

        setupCalculators() {
            this.fields.forEach(field => {
                if (field.calculator && field.calculator.live) {
                    field.calculator.depends_on.forEach(depField => {
                        this.$watch(`formData.${depField}`, () => this.calculateField(field));
                    });
                }
            });
        },

        setupConditionals() {
            // Conditional logic handled during validation and field visibility
        },

        get visibleFields() {
            return this.fields.filter(field => this.isFieldVisible(field.name));
        },

        isFieldVisible(fieldName) {
            const field = this.fields.find(item => item.name === fieldName);

            if (!field || !field.conditional) return true;

            return this.evaluateCondition(field.conditional);
        },

        evaluateCondition(condition) {
            const rules = Array.isArray(condition.rules) && condition.rules.length
                ? condition.rules
                : [{
                    field: condition.depends_on,
                    operator: condition.operator || 'equals',
                    value: condition.value || '',
                }];

            const validRules = rules.filter(rule => rule && rule.field);

            if (!validRules.length) return true;

            const matches = validRules.map(rule => this.checkCondition(
                this.formData[rule.field],
                rule.operator || 'equals',
                rule.value || ''
            ));
            const passed = condition.logic === 'any'
                ? matches.includes(true)
                : !matches.includes(false);

            return condition.action === 'hide' ? !passed : passed;
        },

        checkCondition(value, operator, target) {
            switch (operator) {
                case 'equals': return value == target;
                case 'not_equals': return value != target;
                case 'contains': return String(value).includes(String(target));
                case 'greater_than': return parseFloat(value) > parseFloat(target);
                case 'less_than': return parseFloat(value) < parseFloat(target);
                case 'is_empty': return value === '' || value === null || value === undefined;
                case 'is_not_empty': return value !== '' && value !== null && value !== undefined;
                default: return true;
            }
        },

        calculateField(calculatorField) {
            if (!calculatorField.calculator) return;
            const calc = calculatorField.calculator;
            let formula = calc.formula;

            calc.depends_on.forEach(dep => {
                const val = parseFloat(this.formData[dep]) || 0;
                formula = formula.replace(new RegExp('{'+dep+'}', 'g'), val);
            });

            try {
                const result = this.evalMath(formula);
                const precision = calc.precision || 2;
                const formatted = (Math.round(result * Math.pow(10, precision)) / Math.pow(10, precision)).toFixed(precision);
                const display = calc.prefix + formatted + calc.suffix;
                this.$set(this.formData, calculatorField.name, display);
            } catch (e) {
                console.error('Calc error:', e);
            }
        },

        evalMath(expr) {
            expr = expr.replace(/[^0-9\+\-\*\/\.\(\) ]/g, '');
            if (!expr) return 0;
            try {
                return eval(expr);
            } catch {
                return 0;
            }
        },

        validatePage() {
            this.errors = {};
            let isValid = true;

            this.visibleFields.forEach(field => {
                const requiredMsg = '{{ __('forms.error_required_field') }}'.replace('{label}', field.label);
                if (field.required && (!this.formData[field.name] || this.formData[field.name] === '')) {
                    this.errors[field.name] = requiredMsg;
                    isValid = false;
                }

                if (field.type === 'email' && this.formData[field.name]) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(this.formData[field.name])) {
                        this.errors[field.name] = '{{ __('forms.validation_invalid_email') }}';
                        isValid = false;
                    }
                }

                if (field.type === 'number') {
                    const val = parseFloat(this.formData[field.name]);
                    if (field.options?.min !== undefined && val < field.options.min) {
                        this.errors[field.name] = '{{ __('forms.validation_min') }}'.replace('{min}', field.options.min);
                        isValid = false;
                    }
                    if (field.options?.max !== undefined && val > field.options.max) {
                        this.errors[field.name] = '{{ __('forms.validation_max') }}'.replace('{max}', field.options.max);
                        isValid = false;
                    }
                }

                if (field.type === 'file' && this.formData[field.name]) {
                    const files = this.formData[field.name];
                    const maxSize = field.max_size * 1024;
                    if (Array.isArray(files)) {
                        files.forEach(file => {
                            if (file.size > maxSize) {
                                this.errors[field.name] = '{{ __('forms.validation_file_too_big') }}'.replace('{max}', field.max_size);
                                isValid = false;
                            }
                        });
                    }
                }
            });

            return isValid;
        },

        nextPage() {
            if (!this.validatePage()) return;
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
        },

        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },

        async submitForm() {
            if (!this.validatePage()) return;

            @if($recaptchaEnabled && $recaptchaVersion === 'v3' && $recaptchaSiteKey)
                if (!window.grecaptcha) {
                    this.errors.general = {{ Illuminate\Support\Js::from(__('forms.validation_captcha_unavailable')) }};
                    return;
                }
                const recaptchaToken = await new Promise(resolve => {
                    window.grecaptcha.ready(async () => resolve(await window.grecaptcha.execute(
                        {{ Illuminate\Support\Js::from($recaptchaSiteKey) }},
                        { action: 'form_submit' }
                    )));
                });
                this.$refs.recaptchaToken.value = recaptchaToken;
            @endif

            this.loading = true;
            this.errors = {};

            const formData = new FormData();
            Object.keys(this.formData).forEach(key => {
                const val = this.formData[key];
                if (val instanceof File || (Array.isArray(val) && val[0] instanceof File)) {
                    if (Array.isArray(val)) {
                        val.forEach(file => formData.append(key + '[]', file));
                    } else {
                        formData.append(key, val);
                    }
                } else if (typeof val === 'object') {
                    formData.append(key, JSON.stringify(val));
                } else {
                    formData.append(key, val);
                }
            });
            formData.set('idempotency_key', this.idempotencyKey);
            ['g-recaptcha-response', 'recaptcha_token', 'cf-turnstile-response'].forEach(key => {
                const input = this.$refs.formElement.querySelector(`[name="${key}"]`);
                if (input?.value) formData.set(key, input.value);
            });

            try {
                const response = await fetch(this.$el.querySelector('form').action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.$el.querySelector('input[name="_token"]').value
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        this.errors.general = data.message || '{{ __('forms.error_submission_failed') }}';
                    }
                    return;
                }

                this.submitted = true;

                this.$el.dispatchEvent(new CustomEvent('form-submitted', {
                    detail: { submissionId: data.submission_id, formId: {{ $formId }} }
                }));

            } catch (e) {
                this.errors.general = '{{ __('forms.error_network') }}';
            } finally {
                this.loading = false;
            }
        }
    }));
});
</script>
