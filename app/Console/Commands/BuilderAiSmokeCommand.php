<?php

namespace App\Console\Commands;

use App\Builder\Http\Controllers\AdvancedBuilderController;
use App\Builder\Services\BuilderAiService;
use App\Builder\Services\PageRenderer;
use App\Core\Services\SettingsService;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\SeoMeta;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

/**
 * Smoke-проверка AI-агента конструктора без реального API-ключа.
 *
 * Поднимает локальную OpenAI-совместимую заглушку, генерирует страницу,
 * правит выбранный блок, сохраняет и проверяет, что остальная страница
 * осталась нетронутой. Все изменения откатываются в finally.
 */
class BuilderAiSmokeCommand extends Command
{
    protected $signature = 'builder:ai-smoke
        {--port=8123 : Порт локальной заглушки провайдера}
        {--keep-page : Не удалять тестовую страницу после прогона}';

    protected $description = 'Smoke-проверка AI-агента конструктора на локальной заглушке OpenAI-совместимого провайдера';

    /** @var array<int, array{0: string, 1: string, 2: string}> */
    private array $checks = [];

    public function handle(
        BuilderAiService $ai,
        AdvancedBuilderController $controller,
        PageRenderer $renderer,
        SettingsService $settings,
    ): int {
        $port = (int) $this->option('port');

        if ($port < 1024 || $port > 65535) {
            $this->error('Порт должен быть в диапазоне 1024-65535.');

            return self::FAILURE;
        }

        $stub = base_path('scripts/smoke/ai-provider-stub.php');

        if (! is_file($stub)) {
            $this->error('Не найден файл заглушки: '.$stub);

            return self::FAILURE;
        }

        $backup = DB::table('settings')->where('group_name', 'ai')->get()->map(fn ($row) => (array) $row)->all();
        $stubLog = storage_path('app/ai-smoke-stub-'.uniqid().'.log');
        $createdUser = false;
        $page = null;
        $process = null;

        try {
            $user = User::query()->get()->first(fn (User $candidate) => $candidate->hasPermission('pages.edit'))
                ?? User::query()->first();

            if (! $user) {
                $user = User::query()->forceCreate([
                    'name' => 'AI Smoke',
                    'email' => 'ai-smoke-'.uniqid().'@example.test',
                    'password' => bcrypt(bin2hex(random_bytes(8))),
                    'email_verified_at' => now(),
                ]);
                $createdUser = true;
            }

            $process = new Process(
                [PHP_BINARY, '-S', "127.0.0.1:{$port}", $stub],
                base_path(),
                ['VC_AI_STUB_LOG' => $stubLog],
            );
            $process->setTimeout(null);
            $process->start();

            if (! $this->waitForPort($port, 10.0, $process)) {
                $this->record('Заглушка провайдера запущена', false, trim($process->getOutput().$process->getErrorOutput()));

                return $this->finish();
            }

            $this->record('Заглушка провайдера запущена', true, "127.0.0.1:{$port}");

            $settings->setMany([
                'ai.enabled' => true,
                'ai.default_provider' => 'custom',
                'ai.default_model' => 'smoke-model',
                'ai.custom_api_base' => "http://127.0.0.1:{$port}/v1/chat/completions",
                'ai.custom_api_key' => 'smoke-stub-key',
                'ai.content_language' => 'ru',
                'ai.brand_voice' => 'дружелюбно и по делу',
            ]);
            $settings->forgetCache();

            $slug = 'ai-smoke-'.now()->format('His').'-'.random_int(100, 999);
            $page = Page::query()->create([
                'title' => 'AI smoke check',
                'slug' => $slug,
                'uri' => '/'.$slug,
                'status' => 'draft',
                'content_json' => ['version' => '1.0', 'layout' => 'default', 'sections' => []],
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $save = function (array $sections) use ($controller, $page, $user) {
                $request = Request::create("/admin/pages/{$page->id}/builder/advanced/save", 'POST', [
                    'content' => $sections,
                    'title' => $page->title,
                    'create_revision' => true,
                ]);
                $request->headers->set('Accept', 'application/json');
                $request->setUserResolver(fn () => $user);

                return $controller->saveAdvanced($request, $page);
            };

            // 1. Генерация страницы целиком.
            $generated = $ai->run($page, 'Собери лендинг кофейни с меню и записью', [], [], null, 'page');
            $this->record(
                'Агент сгенерировал страницу',
                $generated['success'] && count($generated['sections'] ?? []) >= 2,
                $generated['success']
                    ? count($generated['sections']).' секций, '.collect($generated['sections'])->sum(fn ($section) => count($section['blocks'] ?? [])).' блоков'
                    : ($generated['error'] ?? 'unknown').': '.($generated['message'] ?? ''),
            );

            if (! $generated['success']) {
                return $this->finish();
            }

            $saveResponse = $save($generated['sections']);
            $this->record('Страница сохранена', $saveResponse->getStatusCode() === 200, 'HTTP '.$saveResponse->getStatusCode());

            $page->refresh();
            $savedSections = $page->content_json['sections'] ?? [];
            $this->record(
                'Секции попали в content_json',
                count($savedSections) >= 2 && str_contains((string) data_get($savedSections, '0.blocks.0.settings.text'), 'Кофейня'),
                'заголовок: '.data_get($savedSections, '0.blocks.0.settings.text'),
            );

            // 2. Точечная правка выбранного блока.
            $faqBefore = data_get($savedSections, '1.blocks.0.settings.items', []);
            $blockIdBefore = data_get($savedSections, '0.blocks.0.id');
            $headingBefore = data_get($savedSections, '0.blocks.0.settings.text');
            $paddingBefore = data_get($savedSections, '0.settings.padding_top');

            $edit = $ai->run($page, 'Сделай заголовок короче', $savedSections, [
                'section_index' => 0,
                'block_index' => 0,
                'block_type' => 'heading',
            ], null, 'block');

            $this->record(
                'Агент переписал выбранный блок',
                $edit['success'] && ($edit['scope'] ?? null) === 'block',
                $edit['success'] ? (($edit['target']['label'] ?? '').' — '.($edit['message'] ?? '')) : (($edit['error'] ?? 'unknown').': '.($edit['message'] ?? '')),
            );

            if (! $edit['success']) {
                return $this->finish();
            }

            $saveResponse = $save($edit['sections']);
            $this->record('Правка сохранена', $saveResponse->getStatusCode() === 200, 'HTTP '.$saveResponse->getStatusCode());

            $page->refresh();
            $edited = $page->content_json['sections'] ?? [];

            $this->record(
                'Заголовок изменился',
                data_get($edited, '0.blocks.0.settings.text') !== $headingBefore
                    && str_contains((string) data_get($edited, '0.blocks.0.settings.text'), 'Зерно'),
                (string) data_get($edited, '0.blocks.0.settings.text'),
            );
            $this->record(
                'Id блока сохранён',
                data_get($edited, '0.blocks.0.id') === $blockIdBefore,
                (string) data_get($edited, '0.blocks.0.id'),
            );
            $this->record(
                'Соседние блоки секции не тронуты',
                data_get($edited, '0.blocks.2.settings.text') === 'Записаться на дегустацию'
                    && data_get($edited, '0.settings.padding_top') === $paddingBefore,
                'кнопка: '.data_get($edited, '0.blocks.2.settings.text').', padding_top: '.data_get($edited, '0.settings.padding_top'),
            );
            $this->record(
                'Вторая секция не тронута',
                data_get($edited, '1.blocks.0.settings.items') === $faqBefore && count($edited) === count($savedSections),
                'FAQ вопросов: '.count((array) data_get($edited, '1.blocks.0.settings.items', [])),
            );
            $this->record(
                'Ревизии созданы',
                PageRevision::query()->where('page_id', $page->id)->count() === 2,
                PageRevision::query()->where('page_id', $page->id)->count().' шт.',
            );

            $html = (string) $renderer->render($page->content_json);
            $this->record(
                'Страница рендерится',
                str_contains($html, (string) data_get($edited, '0.blocks.0.settings.text'))
                    && str_contains($html, 'Есть ли доставка?'),
                strlen($html).' байт HTML',
            );

            $requests = is_file($stubLog) ? substr_count((string) file_get_contents($stubLog), '"model"') : 0;
            $this->record('Провайдер вызван ровно дважды', $requests === 2, $requests.' запрос(ов)');

            if ($this->option('keep-page')) {
                $this->line('Тестовая страница сохранена: '.route('admin.pages.builder', $page));
            }

            return $this->finish();
        } catch (\Throwable $e) {
            $this->record('Прогон без исключений', false, $e->getMessage());

            return $this->finish();
        } finally {
            $process?->stop(1);
            if (is_file($stubLog)) {
                @unlink($stubLog);
            }

            try {
                if ($page && ! $this->option('keep-page')) {
                    PageRevision::query()->where('page_id', $page->id)->delete();
                    SeoMeta::query()->where('entity_type', Page::class)->where('entity_id', $page->id)->delete();
                    $page->delete();
                }

                DB::table('settings')->where('group_name', 'ai')->delete();
                if ($backup !== []) {
                    DB::table('settings')->insert($backup);
                }
                $settings->forgetCache();

                if ($createdUser) {
                    $user->delete();
                }
            } catch (\Throwable $e) {
                $this->warn('Не удалось полностью откатить данные smoke-проверки: '.$e->getMessage());
            }
        }
    }

    private function record(string $name, bool $ok, string $detail = ''): void
    {
        $this->checks[] = [$name, $ok ? 'OK' : 'FAIL', $detail];
    }

    private function finish(): int
    {
        $this->newLine();
        $this->table(['Проверка', 'Результат', 'Детали'], $this->checks);

        $failed = collect($this->checks)->contains(fn (array $check) => $check[1] === 'FAIL');

        if ($failed) {
            $this->error('Smoke-проверка AI-агента провалена.');

            return self::FAILURE;
        }

        $this->info('Smoke-проверка AI-агента пройдена: '.count($this->checks).' проверок.');

        return self::SUCCESS;
    }

    private function waitForPort(int $port, float $timeout, Process $process): bool
    {
        $deadline = microtime(true) + $timeout;

        while (microtime(true) < $deadline) {
            $socket = @fsockopen('127.0.0.1', $port, $errno, $errstr, 0.25);

            if (is_resource($socket)) {
                fclose($socket);

                return true;
            }

            if (! $process->isRunning()) {
                return false;
            }

            usleep(100_000);
        }

        return false;
    }
}
