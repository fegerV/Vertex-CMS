<?php

namespace Tests\Feature;

use App\Builder\Support\BuilderLibraryManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuilderLibraryManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCore();
        $this->markApplicationAsInstalled();
    }

    public function test_builder_library_manager_exposes_builtin_templates_with_visual_metadata(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $manager = app(BuilderLibraryManager::class);
        $request = request()->setUserResolver(fn () => $editor);

        $templates = $manager->visibleTemplates($request);

        $this->assertNotEmpty($templates);
        $this->assertSame('hero-banner', $templates[0]['id']);
        $this->assertSame('builtin', $templates[0]['source']);
        $this->assertArrayHasKey('thumbnail', $templates[0]);
        $this->assertArrayHasKey('sections_count', $templates[0]);
        $this->assertArrayHasKey('blocks_count', $templates[0]);
        $this->assertFalse($templates[0]['can_edit']);
    }

    public function test_builder_library_manager_exposes_quick_add_template_starters(): void
    {
        $manager = app(BuilderLibraryManager::class);
        $templates = $manager->quickAddTemplates();

        $this->assertNotEmpty($templates);
        $this->assertSame('template-hero-heading', $templates[0]['id']);
        $this->assertSame('template', $templates[0]['kind']);
        $this->assertArrayHasKey('blocks', $templates[0]);
        $this->assertSame('heading', $templates[0]['blocks'][0]['type']);
    }
}
