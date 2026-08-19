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

    public function test_icon_rendering()
    {
        $html = $this->service->compileBlock('icon', [
            'icon' => 'star',
            'color' => '#ff0000'
        ]);

        $this->assertStringContainsString('vc-icon', $html);
        $this->assertStringContainsString('color: #ff0000', $html);
    }

    public function test_progress_bar_rendering()
    {
        $html = $this->service->compileBlock('progress-bar', [
            'value' => 75,
            'max' => 100,
            'color' => '#00ff00'
        ]);

        $this->assertStringContainsString('75%', $html);
        $this->assertStringContainsString('background-color: #00ff00', $html);
    }

    public function test_hero_rendering()
    {
        $html = $this->service->compileBlock('hero', [
            'title' => 'Hero Title',
            'subtitle' => 'Hero Subtitle',
            'background' => 'https://example.com/image.jpg',
            'title_color' => '#ffffff',
            'subtitle_color' => '#ffffff',
            'button_text' => 'Click Me',
            'button_url' => 'https://example.com',
            'button_target' => '_blank',
            'button_bg_color' => '#3b82f6',
            'button_text_color' => '#ffffff',
            'button_border_color' => '#1e40af',
            'padding_top' => 80,
            'padding_bottom' => 80
        ]);

        $this->assertStringContainsString('Hero Title', $html);
        $this->assertStringContainsString('Hero Subtitle', $html);
        $this->assertStringContainsString('https://example.com/image.jpg', $html);
        $this->assertStringContainsString('Click Me', $html);
        $this->assertStringContainsString('href="https://example.com"', $html);
        $this->assertStringContainsString('target="_blank"', $html);
        $this->assertStringContainsString('background-color: #3b82f6', $html);
        $this->assertStringContainsString('color: #ffffff', $html);
        $this->assertStringContainsString('padding-top: 80px', $html);
        $this->assertStringContainsString('padding-bottom: 80px', $html);
    }

    public function test_gallery_rendering()
    {
        $html = $this->service->compileBlock('gallery', [
            'images' => [
                ['media_id' => 1, 'alt' => 'Image 1', 'caption' => 'Caption 1'],
                ['media_id' => 2, 'alt' => 'Image 2', 'caption' => 'Caption 2']
            ],
            'layout' => 'slider',
            'columns' => 3,
            'tablet_columns' => 2,
            'mobile_columns' => 1,
            'gap' => 'md',
            'radius' => 'md',
            'aspect_ratio' => '16:9',
            'caption_mode' => 'overlay',
            'lightbox' => true
        ]);

        $this->assertStringContainsString('vc-gallery', $html);
        $this->assertStringContainsString('vc-gallery-layout-slider', $html);
        $this->assertStringContainsString('--vc-gallery-columns: 3', $html);
        $this->assertStringContainsString('--vc-gallery-gap: 1rem', $html);
        $this->assertStringContainsString('--vc-gallery-ratio: 16 / 9', $html);
        $this->assertStringContainsString('vc-gallery-radius-md', $html);
        $this->assertStringContainsString('Image 1', $html);
        $this->assertStringContainsString('Image 2', $html);
        $this->assertStringContainsString('Caption 1', $html);
        $this->assertStringContainsString('data-lightbox="gallery"', $html);
        $this->assertStringContainsString('data-vc-lightbox', $html);
        $this->assertStringContainsString('data-vc-gallery-prev', $html);
    }

    public function test_cart_rendering()
    {
        $html = $this->service->compileBlock('cart', [
            'items' => [
                ['title' => 'Starter plan', 'quantity' => 1, 'price' => 49],
                ['title' => 'Support add-on', 'quantity' => 2, 'price' => 9],
            ],
            'currency' => '$',
            'show_coupon' => true,
            'show_shipping' => true,
        ]);

        $this->assertStringContainsString('Cart Summary', $html);
        $this->assertStringContainsString('Starter plan', $html);
        $this->assertStringContainsString('Support add-on', $html);
        $this->assertStringContainsString('Proceed to checkout', $html);
        $this->assertStringContainsString('Apply', $html);
    }
}
