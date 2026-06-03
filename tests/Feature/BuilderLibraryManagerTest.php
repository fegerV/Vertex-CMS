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

    public function test_builder_library_manager_exposes_design_library_workspace_contract(): void
    {
        $editor = $this->makeUserWithRole('editor');
        $manager = app(BuilderLibraryManager::class);
        $request = request()->setUserResolver(fn () => $editor);

        $workspace = $manager->designLibraryWorkspace($request);

        $this->assertSame('1.0', $workspace['version']);
        $this->assertSame('templates', $workspace['navigation'][0]['id']);
        $this->assertGreaterThan(0, $workspace['stats']['templates']);
        $this->assertGreaterThan(0, $workspace['stats']['starters']);
        $this->assertNotEmpty($workspace['categories']['templates']);
        $this->assertSame('templates', $workspace['collections'][0]['id']);
        $this->assertSame('starters', $workspace['collections'][1]['id']);
        $this->assertArrayHasKey('thumbnail', $workspace['collections'][1]['items'][0]);
        $this->assertSame('presets', $workspace['collections'][2]['id']);
    }
}
