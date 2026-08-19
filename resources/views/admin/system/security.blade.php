@extends('admin.layouts.app')

@section('title', 'Безопасность - VertexCMS')
@section('page_title', 'Безопасность')
@section('page_subtitle', 'Security Core, Alerts, Scanner и Integrity Monitor')

@section('content')
    @php
        $core = $dashboard['core'];
        $totals = $dashboard['totals'];
        $modules = $dashboard['modules'];
        $integrity = $dashboard['integrity'];
        $scanner = $dashboard['scanner'];
        $alerts = $dashboard['alerts'];

        $integrityModule = collect($modules)->firstWhere('key', 'integrity') ?? [
            'severity' => 'muted',
            'status_label' => 'Неизвестно',
        ];
        $scannerModule = collect($modules)->firstWhere('key', 'scanner') ?? [
            'severity' => 'muted',
            'status_label' => 'Неизвестно',
        ];
        $alertsModule = collect($modules)->firstWhere('key', 'alerts') ?? [
            'severity' => 'muted',
            'status_label' => 'Неизвестно',
        ];

        $badgeClasses = [
            'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
            'danger' => 'border-rose-200 bg-rose-50 text-rose-700',
            'muted' => 'border-[var(--vc-border)] bg-[var(--vc-surface-muted)] text-[var(--vc-text-muted)]',
        ];

        $severityClass = fn (string $severity) => $badgeClasses[$severity] ?? $badgeClasses['muted'];
        $formatDate = function (?string $value): string {
            if (! $value) {
                return '—';
            }

            return \Illuminate\Support\Carbon::parse($value)->format('d.m.Y H:i');
        };
    @endphp

    <div class="space-y-6">
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="vc-panel p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Security Core</p>
                <p class="mt-3 text-3xl font-semibold text-[var(--vc-text)]">{{ $core['enabled'] ? 'ON' : 'OFF' }}</p>
                <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Встроенный базовый слой защиты, который поставляется вместе с ядром.</p>
            </article>
            <article class="vc-panel p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Включено модулей</p>
                <p class="mt-3 text-3xl font-semibold text-[var(--vc-text)]">{{ $totals['enabled_modules'] }}</p>
                <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Toggle-модули, активные через конфиг или окружение.</p>
            </article>
            <article class="vc-panel p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Реально реализовано</p>
                <p class="mt-3 text-3xl font-semibold text-[var(--vc-text)]">{{ $totals['implemented_modules'] }}</p>
                <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Runtime-модули с собственной логикой, а не только каркасом.</p>
            </article>
            <article class="vc-panel p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Активные alerts</p>
                <p class="mt-3 text-3xl font-semibold text-[var(--vc-text)]">{{ $totals['active_alerts'] }}</p>
                <p class="mt-2 text-sm text-[var(--vc-text-muted)]">Живые предупреждения по core, scanner, integrity и system health.</p>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <article class="vc-panel p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-[var(--vc-text)]">Активные предупреждения</h2>
                        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Этот блок пересчитывается при каждом открытии dashboard и собирает сигналы из core config, system health, Scanner и Integrity.</p>
                    </div>
                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $severityClass($alertsModule['severity']) }}">
                        {{ $alertsModule['status_label'] }}
                    </span>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($alerts['alerts'] as $alert)
                        <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] px-4 py-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $severityClass($alert['severity']) }}">
                                    {{ strtoupper($alert['severity']) }}
                                </span>
                                <span class="text-xs uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">{{ $alert['source'] }}</span>
                            </div>
                            <p class="mt-3 text-sm font-semibold text-[var(--vc-text)]">{{ $alert['title'] }}</p>
                            <p class="mt-1 text-sm text-[var(--vc-text-muted)]">{{ $alert['message'] }}</p>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-[var(--vc-border)] px-4 py-6 text-sm text-[var(--vc-text-muted)]">
                            Сейчас критичных предупреждений нет. Модуль Alerts находится в режиме мониторинга.
                        </div>
                    @endforelse
                </div>
            </article>

            <article class="vc-panel p-6">
                <div>
                    <h2 class="text-lg font-semibold text-[var(--vc-text)]">Alerts module</h2>
                    <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Реактивный модуль, который агрегирует риски без отдельной таблицы и работает как вычисляемый слой поверх ядра и scan-report'ов.</p>
                </div>

                <div class="mt-5 grid gap-4">
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Всего alerts</p>
                        <p class="mt-2 text-2xl font-semibold text-[var(--vc-text)]">{{ $alerts['counts']['total'] }}</p>
                    </div>
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Danger</p>
                        <p class="mt-2 text-2xl font-semibold text-[var(--vc-text)]">{{ $alerts['counts']['danger'] }}</p>
                    </div>
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Warning</p>
                        <p class="mt-2 text-2xl font-semibold text-[var(--vc-text)]">{{ $alerts['counts']['warning'] }}</p>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                    <p class="font-semibold text-[var(--vc-text)]">Сводка</p>
                    <p class="mt-2 text-sm text-[var(--vc-text-muted)]">{{ $alerts['summary'] }}</p>
                </div>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
            <article class="vc-panel p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-[var(--vc-text)]">Scanner</h2>
                        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Фоновый heuristic scan для uploads и медиатеки. Работает вне request-потока через artisan/schedule и сохраняет последний JSON-report.</p>
                    </div>
                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $severityClass($scannerModule['severity']) }}">
                        {{ $scannerModule['status_label'] }}
                    </span>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Scanned files</p>
                        <p class="mt-2 text-2xl font-semibold text-[var(--vc-text)]">{{ $scanner['scanned_files'] }}</p>
                    </div>
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Findings</p>
                        <p class="mt-2 text-2xl font-semibold text-[var(--vc-text)]">{{ $scanner['counts']['total'] }}</p>
                    </div>
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Danger</p>
                        <p class="mt-2 text-2xl font-semibold text-[var(--vc-text)]">{{ $scanner['counts']['danger'] }}</p>
                    </div>
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Warning</p>
                        <p class="mt-2 text-2xl font-semibold text-[var(--vc-text)]">{{ $scanner['counts']['warning'] }}</p>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                    <p class="font-semibold text-[var(--vc-text)]">Состояние Scanner</p>
                    <p class="mt-2 text-sm text-[var(--vc-text-muted)]">{{ $scanner['summary'] }}</p>
                    <dl class="mt-4 grid gap-3 md:grid-cols-2 text-sm">
                        <div>
                            <dt class="text-[var(--vc-text-muted)]">Последний report</dt>
                            <dd class="mt-1 font-semibold text-[var(--vc-text)]">{{ $formatDate($scanner['last_scanned_at']) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[var(--vc-text-muted)]">Фоновый запуск</dt>
                            <dd class="mt-1 font-semibold text-[var(--vc-text)]"><code>security:scanner:run</code></dd>
                        </div>
                    </dl>
                </div>
            </article>

            <article class="vc-panel p-6">
                <div>
                    <h2 class="text-lg font-semibold text-[var(--vc-text)]">Последние findings</h2>
                    <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Scanner ищет исполняемые файлы в uploads, подозрительный SVG-контент и записи медиатеки без файла на диске.</p>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($scanner['findings'] as $finding)
                        <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] px-4 py-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $severityClass($finding['severity']) }}">
                                    {{ strtoupper($finding['severity']) }}
                                </span>
                                <span class="text-xs uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">{{ $finding['type'] }}</span>
                            </div>
                            <p class="mt-3 break-all text-sm font-semibold text-[var(--vc-text)]">{{ $finding['path'] }}</p>
                            <p class="mt-1 text-sm text-[var(--vc-text-muted)]">{{ $finding['message'] }}</p>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-[var(--vc-border)] px-4 py-6 text-sm text-[var(--vc-text-muted)]">
                            Пока нет findings. После первого фонового прогона scanner здесь появятся свежие результаты.
                        </div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.2fr_1fr]">
            <article class="vc-panel p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-[var(--vc-text)]">Integrity Monitor</h2>
                        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Модуль baseline/drift контроля для кода, шаблонов и конфигурации.</p>
                    </div>
                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $severityClass($integrityModule['severity']) }}">
                        {{ $integrityModule['status_label'] }}
                    </span>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Tracked files</p>
                        <p class="mt-2 text-2xl font-semibold text-[var(--vc-text)]">{{ $integrity['tracked_files'] }}</p>
                    </div>
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Changed</p>
                        <p class="mt-2 text-2xl font-semibold text-[var(--vc-text)]">{{ $integrity['changed_count'] }}</p>
                    </div>
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Added</p>
                        <p class="mt-2 text-2xl font-semibold text-[var(--vc-text)]">{{ $integrity['added_count'] }}</p>
                    </div>
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Removed</p>
                        <p class="mt-2 text-2xl font-semibold text-[var(--vc-text)]">{{ $integrity['removed_count'] }}</p>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                    <p class="font-semibold text-[var(--vc-text)]">Состояние Integrity</p>
                    <p class="mt-2 text-sm text-[var(--vc-text-muted)]">{{ $integrity['summary'] }}</p>
                    <dl class="mt-4 grid gap-3 md:grid-cols-2 text-sm">
                        <div>
                            <dt class="text-[var(--vc-text-muted)]">Baseline создан</dt>
                            <dd class="mt-1 font-semibold text-[var(--vc-text)]">{{ $formatDate($integrity['baseline_created_at']) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[var(--vc-text-muted)]">Последний scan</dt>
                            <dd class="mt-1 font-semibold text-[var(--vc-text)]">{{ $formatDate($integrity['last_scanned_at']) }}</dd>
                        </div>
                    </dl>
                </div>

                @if ($integrity['enabled'])
                    <div class="mt-5 flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('admin.system.security.integrity.baseline') }}">
                            @csrf
                            <button class="vc-button vc-button-secondary px-4 py-3">
                                {{ $integrity['baseline_exists'] ? 'Переинициализировать baseline' : 'Инициализировать baseline' }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('admin.system.security.integrity.scan') }}">
                            @csrf
                            <button class="vc-button vc-button-primary px-4 py-3" {{ $integrity['baseline_exists'] ? '' : 'disabled' }}>
                                Запустить scan
                            </button>
                        </form>
                    </div>
                @endif
            </article>

            <article class="vc-panel p-6">
                <div>
                    <h2 class="text-lg font-semibold text-[var(--vc-text)]">Последние изменения</h2>
                    <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Компактный diff по drift-изменениям относительно baseline.</p>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse ($integrity['recent_changes'] as $change)
                        @php
                            $changeSeverity = match ($change['type']) {
                                'changed' => 'warning',
                                'added' => 'success',
                                'removed' => 'danger',
                                default => 'muted',
                            };
                        @endphp
                        <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] px-4 py-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $severityClass($changeSeverity) }}">
                                    {{ strtoupper($change['type']) }}
                                </span>
                                <span class="text-xs text-[var(--vc-text-soft)]">{{ isset($change['size']) ? number_format((int) $change['size']) . ' B' : '—' }}</span>
                            </div>
                            <p class="mt-3 break-all text-sm font-semibold text-[var(--vc-text)]">{{ $change['path'] ?? 'unknown' }}</p>
                            @if (! empty($change['modified_at']))
                                <p class="mt-1 text-xs text-[var(--vc-text-muted)]">Изменен: {{ $formatDate($change['modified_at']) }}</p>
                            @endif
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-[var(--vc-border)] px-4 py-6 text-sm text-[var(--vc-text-muted)]">
                            Недавних изменений нет. После первого scan здесь появится компактный diff по файлам.
                        </div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.1fr_1.4fr]">
            <article class="vc-panel p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-[var(--vc-text)]">Security Core</h2>
                        <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Всегда активный слой, который не должен выпадать из поставки ядра.</p>
                    </div>
                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $severityClass($core['enabled'] ? 'success' : 'danger') }}">
                        {{ $core['enabled'] ? 'Активен' : 'Отключен' }}
                    </span>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Middleware</p>
                        <ul class="mt-3 space-y-2 text-sm text-[var(--vc-text)]">
                            @foreach ($core['middleware'] as $label => $class)
                                <li>
                                    <span class="font-semibold">{{ str_replace('_', ' ', ucfirst($label)) }}</span>
                                    <div class="mt-1 break-all text-xs text-[var(--vc-text-muted)]">{{ $class }}</div>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-[var(--vc-text-soft)]">Policy</p>
                        <dl class="mt-3 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-[var(--vc-text-muted)]">Пароль min length</dt>
                                <dd class="font-semibold text-[var(--vc-text)]">{{ $core['password_min_length'] }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-[var(--vc-text-muted)]">Session rotation</dt>
                                <dd class="font-semibold text-[var(--vc-text)]">{{ $core['session_rotation_minutes'] }} мин</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-[var(--vc-text-muted)]">Cache fallback</dt>
                                <dd class="font-semibold text-[var(--vc-text)]">{{ $core['cache_driver'] }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-[var(--vc-text-muted)]">Queue fallback</dt>
                                <dd class="font-semibold text-[var(--vc-text)]">{{ $core['queue_driver'] }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-[var(--vc-text-muted)]">Audit</dt>
                                <dd class="font-semibold text-[var(--vc-text)]">{{ $core['audit_enabled'] ? 'Включен' : 'Выключен' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </article>

            <article class="vc-panel p-6">
                <div>
                    <h2 class="text-lg font-semibold text-[var(--vc-text)]">Статус модулей</h2>
                    <p class="mt-1 text-sm text-[var(--vc-text-muted)]">Core остается встроенным, а тяжелые функции живут как toggle-модули в том же пространстве имен.</p>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    @foreach ($modules as $module)
                        <div class="rounded-2xl border border-[var(--vc-border)] bg-[var(--vc-surface-muted)] p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-semibold text-[var(--vc-text)]">{{ $module['name'] }}</h3>
                                    <p class="mt-1 text-sm text-[var(--vc-text-muted)]">{{ $module['description'] }}</p>
                                </div>
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $severityClass($module['severity']) }}">
                                    {{ $module['status_label'] }}
                                </span>
                            </div>

                            <p class="mt-4 text-sm text-[var(--vc-text-muted)]">{{ $module['summary'] }}</p>

                            <div class="mt-4 flex flex-wrap gap-2 text-xs">
                                <span class="inline-flex rounded-full border border-[var(--vc-border)] px-2.5 py-1 text-[var(--vc-text-muted)]">
                                    {{ $module['enabled'] ? 'Toggle ON' : 'Toggle OFF' }}
                                </span>
                                <span class="inline-flex rounded-full border border-[var(--vc-border)] px-2.5 py-1 text-[var(--vc-text-muted)]">
                                    {{ $module['implemented'] ? 'Runtime ready' : 'Scaffold only' }}
                                </span>
                            </div>

                            @if ($module['key'] === 'integrity')
                                <dl class="mt-4 grid gap-3 sm:grid-cols-2 text-sm">
                                    <div>
                                        <dt class="text-[var(--vc-text-muted)]">Файлов в baseline</dt>
                                        <dd class="mt-1 font-semibold text-[var(--vc-text)]">{{ $module['details']['tracked_files'] }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-[var(--vc-text-muted)]">Последний scan</dt>
                                        <dd class="mt-1 font-semibold text-[var(--vc-text)]">{{ $formatDate($module['details']['last_scanned_at']) }}</dd>
                                    </div>
                                </dl>
                            @elseif ($module['key'] === 'scanner')
                                <dl class="mt-4 grid gap-3 sm:grid-cols-2 text-sm">
                                    <div>
                                        <dt class="text-[var(--vc-text-muted)]">Scanned files</dt>
                                        <dd class="mt-1 font-semibold text-[var(--vc-text)]">{{ $module['details']['scanned_files'] }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-[var(--vc-text-muted)]">Последний report</dt>
                                        <dd class="mt-1 font-semibold text-[var(--vc-text)]">{{ $formatDate($module['details']['last_scanned_at']) }}</dd>
                                    </div>
                                </dl>
                            @elseif ($module['key'] === 'alerts')
                                <dl class="mt-4 grid gap-3 sm:grid-cols-3 text-sm">
                                    <div>
                                        <dt class="text-[var(--vc-text-muted)]">Всего</dt>
                                        <dd class="mt-1 font-semibold text-[var(--vc-text)]">{{ $module['details']['total'] }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-[var(--vc-text-muted)]">Danger</dt>
                                        <dd class="mt-1 font-semibold text-[var(--vc-text)]">{{ $module['details']['danger'] }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-[var(--vc-text-muted)]">Warning</dt>
                                        <dd class="mt-1 font-semibold text-[var(--vc-text)]">{{ $module['details']['warning'] }}</dd>
                                    </div>
                                </dl>
                            @endif
                        </div>
                    @endforeach
                </div>
            </article>
        </section>
    </div>
@endsection
