<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageRevision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuilderAiSmokeCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCore();
        $this->markApplicationAsInstalled();
    }

    public function test_builder_ai_smoke_command_passes_and_cleans_up_after_itself(): void
    {
        $this->makeUserWithRole('editor');

        $this->artisan('builder:ai-smoke', ['--port' => random_int(8300, 8600)])
            ->expectsOutputToContain('Smoke-проверка AI-агента пройдена')
            ->assertExitCode(0);

        $this->assertSame(0, Page::query()->count(), 'Smoke page must be removed.');
        $this->assertSame(0, PageRevision::query()->count(), 'Smoke revisions must be removed.');
    }

    public function test_builder_ai_smoke_command_rejects_invalid_port(): void
    {
        $this->artisan('builder:ai-smoke', ['--port' => 80])
            ->expectsOutputToContain('Порт должен быть в диапазоне')
            ->assertExitCode(1);
    }
}
