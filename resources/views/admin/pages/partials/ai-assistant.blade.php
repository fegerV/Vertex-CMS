@php
    $aiEnabled = (bool) config_value('ai.enabled', false);
    $aiProvider = config_value('ai.default_provider', 'openai');
    $aiModel = config_value('ai.default_model', '');
    $aiConfigured = filled(config_value('ai.openai_api_key')) || filled(config_value('ai.anthropic_api_key'));
@endphp
<aside class="rounded-lg border border-slate-200 bg-white p-5">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold">AI Assistant</h2>
            <p class="mt-1 text-sm text-slate-500">Текст, оформление и SEO-подсказки для страницы.</p>
        </div>
        <span class="rounded-full px-2 py-1 text-xs font-medium {{ $aiEnabled ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
            {{ $aiEnabled ? 'enabled' : 'disabled' }}
        </span>
    </div>

    <div class="mt-4 rounded-md border border-slate-200 bg-slate-50 p-3 text-sm">
        <p><span class="font-medium">Provider:</span> {{ $aiProvider }}</p>
        <p class="mt-1"><span class="font-medium">Model:</span> {{ $aiModel ?: 'не задана' }}</p>
        <p class="mt-1"><span class="font-medium">API key:</span> {{ $aiConfigured ? 'настроен' : 'не настроен' }}</p>
    </div>

    <div class="mt-4 grid gap-2">
        @foreach (['Написать текст', 'Предложить оформление', 'Сгенерировать SEO описание'] as $action)
            <button
                type="button"
                class="rounded-md border border-slate-300 px-3 py-2 text-left text-sm hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                @disabled(! $aiEnabled || ! $aiConfigured)
            >
                {{ $action }}
            </button>
        @endforeach
    </div>

    <label class="mt-4 block">
        <span class="mb-1 block text-sm font-medium">Запрос к AI</span>
        <textarea
            rows="6"
            placeholder="Например: напиши hero-блок для страницы услуг и предложи SEO description."
            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-slate-900"
            @disabled(! $aiEnabled || ! $aiConfigured)
        ></textarea>
    </label>

    <div class="mt-4 rounded-md border border-dashed border-slate-300 p-3 text-sm text-slate-500">
        {{ $aiEnabled && $aiConfigured
            ? 'UI для AI уже встроен в редактор. Интеграция реального провайдера и endpoint чата будет следующим шагом.'
            : 'Включите AI-модуль и задайте API key в настройках, чтобы подготовить страницу к работе с нейросетью.' }}
    </div>
</aside>
