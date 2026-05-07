<?php

namespace Tests\Feature;

use App\Builder\Services\PageBuilderService;
use Tests\TestCase;

class BlockRenderingTest extends TestCase
{
    protected PageBuilderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PageBuilderService::class);
    }

    public function test_heading_rendering()
    {
        $html = $this->service->compileBlock('heading', [
            'level' => 'h1',
            'text' => 'Hello World',
            'align' => 'center',
            'color' => '#ff0000',
            'font_size' => '2rem'
        ]);

        $this->assertStringContainsString('<h1', $html);
        $this->assertStringContainsString('Hello World', $html);
        $this->assertStringContainsString('text-align: center', $html);
        $this->assertStringContainsString('color: #ff0000', $html);
        $this->assertStringContainsString('font-size: 2rem', $html);
    }

    public function test_text_rendering()
    {
        $html = $this->service->compileBlock('text', [
            'content' => "Line 1\nLine 2",
            'align' => 'right'
        ]);

        $this->assertStringContainsString('Line 1<br', $html);
        $this->assertStringContainsString('Line 2', $html);
        $this->assertStringContainsString('text-align: right', $html);
    }

    public function test_button_rendering()
    {
        $html = $this->service->compileBlock('button', [
            'text' => 'Click Me',
            'url' => 'https://example.com',
            'style' => 'primary',
            'size' => 'lg'
        ]);

        $this->assertStringContainsString('href="https://example.com"', $html);
        $this->assertStringContainsString('Click Me', $html);
        $this->assertStringContainsString('bg-blue-600', $html);
        $this->assertStringContainsString('px-6 py-3', $html);
    }

    public function test_image_rendering()
    {
        $html = $this->service->compileBlock('image', [
            'url' => 'https://example.com/image.jpg',
            'alt' => 'Test Image',
            'radius' => 'full',
            'shadow' => 'lg'
        ]);

        $this->assertStringContainsString('src="https://example.com/image.jpg"', $html);
        $this->assertStringContainsString('alt="Test Image"', $html);
        $this->assertStringContainsString('rounded-full', $html);
        $this->assertStringContainsString('shadow-lg', $html);
    }

    public function test_list_rendering()
    {
        $html = $this->service->compileBlock('list', [
            'type' => 'decimal',
            'items' => [
                ['content' => 'Item 1'],
                ['content' => 'Item 2']
            ]
        ]);

        $this->assertStringContainsString('<ol', $html);
        $this->assertStringContainsString('list-decimal', $html);
        $this->assertStringContainsString('Item 1', $html);
        $this->assertStringContainsString('Item 2', $html);
    }

    public function test_faq_rendering()
    {
        $html = $this->service->compileBlock('faq', [
            'items' => [
                ['question' => 'Q1', 'answer' => 'A1']
            ]
        ]);

        $this->assertStringContainsString('Q1', $html);
        $this->assertStringContainsString('A1', $html);
    }
}
