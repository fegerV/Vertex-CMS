<?php

namespace Tests\Feature;

use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaLibraryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedCore();
        $this->markApplicationAsInstalled();
    }

    public function test_media_library_filters_and_sorts_grid_results(): void
    {
        $editor = $this->makeUserWithRole('editor');

        $this->createMedia('zebra.jpg', 'image/jpeg', 400);
        $this->createMedia('alpha.png', 'image/png', 100);
        $this->createMedia('manual.pdf', 'application/pdf', 800);

        $images = $this->actingAs($editor)->getJson('/admin/api/media?type=image&sort=name_asc');

        $images->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.original_filename', 'alpha.png')
            ->assertJsonPath('data.1.original_filename', 'zebra.jpg');

        $pdfs = $this->actingAs($editor)->getJson('/admin/api/media?type=pdf&sort=size_desc');

        $pdfs->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.original_filename', 'manual.pdf');
    }

    public function test_media_library_rejects_unknown_filter_and_sort_values(): void
    {
        $editor = $this->makeUserWithRole('editor');

        $this->actingAs($editor)
            ->getJson('/admin/api/media?type=archive&sort=random')
            ->assertUnprocessable()
            ->assertJsonPath('error.code', 'validation_error')
            ->assertJsonStructure(['error' => ['details' => ['type', 'sort']]]);
    }

    private function createMedia(string $name, string $mimeType, int $size): Media
    {
        return Media::query()->create([
            'disk' => 'public',
            'filename' => $name,
            'original_filename' => $name,
            'mime_type' => $mimeType,
            'extension' => pathinfo($name, PATHINFO_EXTENSION),
            'size' => $size,
            'path' => 'media/'.$name,
            'url' => '/storage/media/'.$name,
            'title' => pathinfo($name, PATHINFO_FILENAME),
        ]);
    }
}
