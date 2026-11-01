<?php

namespace Tests\Feature;

use App\Builder\Services\PageRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlockRenderingTest extends TestCase
{
    use RefreshDatabase;

    protected function renderBlock(string $type, array $settings): string
    {
        return (string) app(PageRenderer::class)->render([
            'version' => '1.0',
            'layout' => 'default',
            'sections' => [[
                'id' => 'section-1',
                'settings' => [],
                'blocks' => [[
                    'id' => 'block-1',
                    'type' => $type,
                    'settings' => $settings,
                ]],
            ]],
        ]);
    }

    public function test_heading_rendering(): void
    {
        $html = $this->renderBlock('heading', [
            'level' => 'h1',
            'text' => 'Hello World',
            'align' => 'center',
            'color' => '#ff0000',
            'font_size' => '2rem',
        ]);

        $this->assertStringContainsString('<h1', $html);
        $this->assertStringContainsString('Hello World', $html);
        $this->assertStringContainsString('text-align: center', $html);
        $this->assertStringContainsString('color: #ff0000', $html);
        $this->assertStringContainsString('font-size: 2rem', $html);
    }

    public function test_text_rendering(): void
    {
        $html = $this->renderBlock('text', [
            'content' => "Line 1\nLine 2",
            'align' => 'right',
        ]);

        $this->assertStringContainsString('Line 1<br', $html);
        $this->assertStringContainsString('Line 2', $html);
        $this->assertStringContainsString('text-align: right', $html);
    }

    public function test_button_rendering(): void
    {
        $html = $this->renderBlock('button', [
            'text' => 'Click Me',
            'url' => 'https://example.com',
            'style' => 'primary',
        ]);

        $this->assertStringContainsString('href="https://example.com"', $html);
        $this->assertStringContainsString('Click Me', $html);
        $this->assertStringContainsString('vc-button vc-button-primary', $html);
    }

    public function test_image_rendering_and_placeholder(): void
    {
        $html = $this->renderBlock('image', [
            'url' => 'https://example.com/image.jpg',
            'alt' => 'Test Image',
            'height' => 120,
            'object_fit' => 'cover',
        ]);

        $this->assertStringContainsString('src="https://example.com/image.jpg"', $html);
        $this->assertStringContainsString('alt="Test Image"', $html);
        $this->assertStringContainsString('height: 120px', $html);
        $this->assertStringContainsString('object-fit: cover', $html);

        $placeholder = $this->renderBlock('image', []);
        $this->assertStringContainsString('vc-image-placeholder', $placeholder);
    }

    public function test_list_rendering(): void
    {
        $html = $this->renderBlock('list', [
            'type' => 'decimal',
            'items' => [
                ['content' => 'Item 1'],
                ['content' => 'Item 2'],
            ],
        ]);

        $this->assertStringContainsString('<ol', $html);
        $this->assertStringContainsString('vc-list-decimal', $html);
        $this->assertStringContainsString('Item 1', $html);
        $this->assertStringContainsString('Item 2', $html);
    }

    public function test_faq_rendering(): void
    {
        $html = $this->renderBlock('faq', [
            'items' => [
                ['question' => 'Q1', 'answer' => 'A1'],
            ],
        ]);

        $this->assertStringContainsString('<details class="vc-faq-item">', $html);
        $this->assertStringContainsString('Q1', $html);
        $this->assertStringContainsString('A1', $html);
    }

    public function test_icon_rendering(): void
    {
        $html = $this->renderBlock('icon', [
            'icon' => 'star',
            'size' => 'lg',
            'color' => '#ff0000',
            'background' => '#fef3c7',
            'radius' => 'full',
        ]);

        $this->assertStringContainsString('vc-icon vc-icon-lg vc-icon-radius-full', $html);
        $this->assertStringContainsString('color: #ff0000', $html);
        $this->assertStringContainsString('background-color: #fef3c7', $html);
        $this->assertStringContainsString('<svg', $html);
    }

    public function test_counter_rendering(): void
    {
        $html = $this->renderBlock('counter', [
            'value' => 250,
            'prefix' => '',
            'suffix' => '+',
            'duration' => 1500,
            'label' => 'Projects completed',
        ]);

        $this->assertStringContainsString('data-vc-counter', $html);
        $this->assertStringContainsString('data-value="250"', $html);
        $this->assertStringContainsString('data-duration="1500"', $html);
        $this->assertStringContainsString('vc-counter-value', $html);
        $this->assertStringContainsString('+', $html);
        $this->assertStringContainsString('Projects completed', $html);
    }

    public function test_progress_bar_rendering(): void
    {
        $html = $this->renderBlock('progress-bar', [
            'value' => 75,
            'max' => 100,
            'color' => '#00ff00',
            'height' => 12,
        ]);

        $this->assertStringContainsString('vc-progress-bar', $html);
        $this->assertStringContainsString('75.0%', $html);
        $this->assertStringContainsString('width: 75%', $html);
        $this->assertStringContainsString('background-color: #00ff00', $html);
        $this->assertStringContainsString('height: 12px', $html);
        $this->assertStringContainsString('aria-valuenow="75"', $html);
    }

    public function test_alert_rendering(): void
    {
        $html = $this->renderBlock('alert', [
            'type' => 'warning',
            'title' => 'Heads up',
            'content' => 'Something happened.',
            'closable' => true,
        ]);

        $this->assertStringContainsString('vc-alert vc-alert-warning', $html);
        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('Heads up', $html);
        $this->assertStringContainsString('Something happened.', $html);
        $this->assertStringContainsString('data-vc-alert-close', $html);
    }

    public function test_breadcrumbs_rendering(): void
    {
        $html = $this->renderBlock('breadcrumbs', [
            'items' => [
                ['title' => 'Home', 'url' => '/'],
                ['title' => 'Current page', 'url' => null],
            ],
            'separator' => '›',
        ]);

        $this->assertStringContainsString('<nav class="vc-breadcrumbs"', $html);
        $this->assertStringContainsString('<a href="/">Home</a>', $html);
        $this->assertStringContainsString('aria-current="page"', $html);
        $this->assertStringContainsString('›', $html);
    }

    public function test_accordion_rendering(): void
    {
        $html = $this->renderBlock('accordion', [
            'items' => [
                ['title' => 'Panel one', 'content' => 'First body', 'open' => true],
                ['title' => 'Panel two', 'content' => 'Second body', 'open' => false],
            ],
            'allow_multiple' => false,
        ]);

        $this->assertStringContainsString('data-vc-accordion', $html);
        $this->assertStringContainsString('data-allow-multiple="false"', $html);
        $this->assertStringContainsString('<details class="vc-accordion-item" open>', $html);
        $this->assertStringContainsString('Panel two', $html);
        $this->assertStringContainsString('Second body', $html);
    }

    public function test_tabs_rendering(): void
    {
        $html = $this->renderBlock('tabs', [
            'tabs' => [
                ['title' => 'Tab one', 'content' => 'Tab content one'],
                ['title' => 'Tab two', 'content' => 'Tab content two'],
            ],
            'style' => 'pill',
            'alignment' => 'center',
        ]);

        $this->assertStringContainsString('vc-tabs vc-tabs-pill vc-tabs-center', $html);
        $this->assertStringContainsString('role="tablist"', $html);
        $this->assertStringContainsString('vc-tab is-active', $html);
        $this->assertStringContainsString('aria-selected="true"', $html);
        $this->assertStringContainsString('Tab content two', $html);
    }

    public function test_collapse_and_modal_and_tooltip_rendering(): void
    {
        $collapse = $this->renderBlock('collapse', [
            'title' => 'More details',
            'content' => 'Hidden content',
            'open' => true,
        ]);

        $this->assertStringContainsString('<details class="vc-collapse" open>', $collapse);
        $this->assertStringContainsString('Hidden content', $collapse);

        $modal = $this->renderBlock('modal', [
            'trigger_text' => 'Open dialog',
            'title' => 'Dialog title',
            'content' => 'Dialog body',
            'size' => 'lg',
        ]);

        $this->assertStringContainsString('data-vc-modal-open="vc-modal-', $modal);
        $this->assertStringContainsString('<dialog class="vc-modal vc-modal-lg"', $modal);
        $this->assertStringContainsString('Dialog title', $modal);
        $this->assertStringContainsString('Dialog body', $modal);

        $tooltip = $this->renderBlock('tooltip', [
            'text' => 'Hover me',
            'content' => 'Tooltip content',
            'position' => 'bottom',
        ]);

        $this->assertStringContainsString('vc-tooltip', $tooltip);
        $this->assertStringContainsString('role="tooltip"', $tooltip);
        $this->assertStringContainsString('vc-tooltip-bottom', $tooltip);
        $this->assertStringContainsString('Tooltip content', $tooltip);
    }

    public function test_testimonials_rendering(): void
    {
        $html = $this->renderBlock('testimonials', [
            'testimonials' => [
                [
                    'text' => 'Great product',
                    'author' => 'Jane Doe',
                    'role' => 'CTO',
                    'rating' => 4,
                    'avatar' => 'https://example.com/avatar.jpg',
                ],
            ],
            'show_rating' => true,
        ]);

        $this->assertStringContainsString('vc-testimonials', $html);
        $this->assertStringContainsString('Great product', $html);
        $this->assertStringContainsString('Jane Doe', $html);
        $this->assertStringContainsString('CTO', $html);
        $this->assertStringContainsString('https://example.com/avatar.jpg', $html);
        $this->assertSame(4, substr_count($html, 'vc-star is-active'));
    }

    public function test_pricing_table_rendering(): void
    {
        $html = $this->renderBlock('pricing-table', [
            'columns' => 2,
            'plans' => [
                [
                    'name' => 'Starter',
                    'price' => 19,
                    'currency' => '$',
                    'period' => 'mo',
                    'features' => ['1 project', 'Email support'],
                    'button_text' => 'Choose Starter',
                    'button_url' => '/checkout/starter',
                ],
                [
                    'name' => 'Pro',
                    'price' => 49,
                    'highlighted' => true,
                ],
            ],
        ]);

        $this->assertStringContainsString('--vc-pricing-columns: 2', $html);
        $this->assertStringContainsString('Starter', $html);
        $this->assertStringContainsString('/mo', $html);
        $this->assertStringContainsString('1 project', $html);
        $this->assertStringContainsString('Choose Starter', $html);
        $this->assertStringContainsString('href="/checkout/starter"', $html);
        $this->assertStringContainsString('is-highlighted', $html);
    }

    public function test_product_card_and_list_rendering(): void
    {
        $card = $this->renderBlock('product-card', [
            'title' => 'Starter plan',
            'description' => 'Everything you need to launch.',
            'price' => 49,
            'old_price' => 69,
            'currency' => '$',
            'rating' => 5,
            'reviews_count' => 12,
            'button_text' => 'Add to cart',
            'image' => 7,
        ]);

        $this->assertStringContainsString('vc-product-card', $card);
        $this->assertStringContainsString('Starter plan', $card);
        $this->assertStringContainsString('src="/api/media/7"', $card);
        $this->assertStringContainsString('vc-product-old-price', $card);
        $this->assertStringContainsString('49.00 $', $card);
        $this->assertStringContainsString('(12)', $card);
        $this->assertStringContainsString('Add to cart', $card);

        $list = $this->renderBlock('product-list', [
            'columns' => 3,
            'products' => [
                ['title' => 'One', 'price' => 10],
                ['title' => 'Two', 'price' => 20],
            ],
        ]);

        $this->assertStringContainsString('--vc-product-columns: 3', $list);
        $this->assertStringContainsString('One', $list);
        $this->assertStringContainsString('20.00', $list);
    }

    public function test_news_feed_renders_only_published_pages(): void
    {
        $this->createPage([
            'title' => 'Published story',
            'slug' => 'published-story',
            'uri' => '/published-story',
            'published_at' => now()->subDay(),
        ], ['description' => 'A short published summary.']);

        $this->createPage([
            'title' => 'Draft story',
            'slug' => 'draft-story',
            'uri' => '/draft-story',
            'status' => 'draft',
            'published_at' => null,
        ]);

        $html = $this->renderBlock('news-feed', [
            'count' => 3,
            'columns' => 2,
            'show_date' => true,
            'show_excerpt' => true,
        ]);

        $this->assertStringContainsString('vc-news-feed', $html);
        $this->assertStringContainsString('--vc-news-columns: 2', $html);
        $this->assertStringContainsString('Published story', $html);
        $this->assertStringContainsString('A short published summary.', $html);
        $this->assertStringContainsString('/published-story', $html);
        $this->assertStringNotContainsString('Draft story', $html);
    }

    public function test_seo_meta_block_renders_nothing_on_frontend(): void
    {
        $html = $this->renderBlock('seo-meta', [
            'title' => 'Custom title',
            'description' => 'Custom description',
        ]);

        $this->assertStringNotContainsString('<meta', $html);
        $this->assertStringNotContainsString('<title', $html);
        $this->assertStringNotContainsString('Custom title', $html);
    }

    public function test_unknown_block_types_do_not_break_rendering(): void
    {
        $html = $this->renderBlock('unknown-widget', ['foo' => 'bar']);

        $this->assertStringContainsString('Unknown VertexCMS block: unknown-widget', $html);
    }

    public function test_html_block_is_sanitized_and_values_are_escaped(): void
    {
        $html = $this->renderBlock('html', [
            'html' => '<p onclick="alert(1)">Safe</p><script>alert(2)</script><a href="javascript:alert(3)">Link</a>',
        ]);

        $this->assertStringContainsString('Safe', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('javascript:', $html);

        $escaped = $this->renderBlock('text', ['text' => '<script>alert(1)</script>']);

        $this->assertStringContainsString('&lt;script&gt;', $escaped);
        $this->assertStringNotContainsString('<script>', $escaped);
    }

    public function test_video_block_builds_provider_embeds(): void
    {
        $youtube = $this->renderBlock('video', [
            'type' => 'youtube',
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'ratio' => '4:3',
            'muted' => true,
            'loop' => true,
        ]);

        $this->assertStringContainsString('https://www.youtube.com/embed/dQw4w9WgXcQ?mute=1', $youtube);
        $this->assertStringContainsString('mute=1', $youtube);
        $this->assertStringContainsString('playlist=dQw4w9WgXcQ', $youtube);
        $this->assertStringContainsString('aspect-ratio: 4 / 3', $youtube);

        $vimeo = $this->renderBlock('video', [
            'type' => 'vimeo',
            'url' => 'https://vimeo.com/76979871',
            'controls' => false,
        ]);

        $this->assertStringContainsString('https://player.vimeo.com/video/76979871', $vimeo);
        $this->assertStringContainsString('pointer-events: none', $vimeo);

        $html5 = $this->renderBlock('video', [
            'type' => 'html5',
            'url' => '/storage/media/clip.mp4',
            'autoplay' => true,
            'muted' => true,
            'controls' => true,
        ]);

        $this->assertStringContainsString('<video src="/storage/media/clip.mp4"', $html5);
        $this->assertStringContainsString('autoplay muted', $html5);
        $this->assertStringContainsString(' controls', $html5);

        $placeholder = $this->renderBlock('video', ['type' => 'youtube', 'url' => '']);

        $this->assertStringContainsString('vc-video-placeholder', $placeholder);
        $this->assertStringNotContainsString('<iframe', $placeholder);
    }

    public function test_video_block_rejects_script_urls(): void
    {
        $html = $this->renderBlock('video', [
            'type' => 'html5',
            'url' => 'javascript:alert(1)',
        ]);

        $this->assertStringNotContainsString('<video', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString('vc-video-placeholder', $html);
    }

    public function test_divider_block_applies_style_options(): void
    {
        $html = $this->renderBlock('divider', [
            'style' => 'dashed',
            'color' => '#ff0000',
            'thickness' => 3,
            'width' => '50%',
        ]);

        $this->assertStringContainsString('border-top: 3px dashed #ff0000', $html);
        $this->assertStringContainsString('width: 50%', $html);
        $this->assertStringContainsString('vc-divider-dashed', $html);

        $sanitized = $this->renderBlock('divider', [
            'style' => 'expression(alert(1))',
            'color' => 'red; background:url(evil)',
            'thickness' => 999,
            'width' => 'calc(100% - 1px)',
        ]);

        $this->assertStringContainsString('border-top: 20px solid #e2e8f0', $sanitized);
        $this->assertStringContainsString('width: 100%', $sanitized);
        $this->assertStringNotContainsString('url(', $sanitized);
    }

    public function test_hero_block_escapes_settings(): void
    {
        $html = $this->renderBlock('hero', [
            'title' => 'Big <script>alert(1)</script>',
            'subtitle' => 'Sub',
            'button_text' => 'Go',
            'button_url' => '/start',
            'background' => 'https://cdn.example.com/bg.jpg',
            'padding_top' => 40,
        ]);

        $this->assertStringContainsString('vc-hero-title', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert', $html);
        $this->assertStringContainsString('background-image', $html);
        $this->assertStringContainsString('padding-top: 40px', $html);
    }

    public function test_columns_and_container_render_nested_blocks(): void
    {
        $columns = $this->renderBlock('columns', [
            'count' => 2,
            'columns' => [
                ['blocks' => [['type' => 'heading', 'settings' => ['text' => 'Left']]]],
                ['blocks' => [['type' => 'text', 'settings' => ['text' => 'Right']]]],
            ],
        ]);

        $this->assertStringContainsString('vc-columns', $columns);
        $this->assertStringContainsString('Left', $columns);
        $this->assertStringContainsString('Right', $columns);
        $this->assertSame(3, substr_count($columns, 'vc-column'));

        $container = $this->renderBlock('container', [
            'max_width' => '3xl',
            'padding' => ['top' => 24, 'bottom' => 8, 'left' => 0, 'right' => 0],
            'blocks' => [['type' => 'heading', 'settings' => ['text' => 'Nested']]],
        ]);

        $this->assertStringContainsString('vc-container-block', $container);
        $this->assertStringContainsString('Nested', $container);
        $this->assertStringContainsString('padding-top: 24px', $container);
    }

    public function test_spacer_block_uses_pixel_height(): void
    {
        $html = $this->renderBlock('spacer', ['height' => 120]);

        $this->assertStringContainsString('vc-spacer', $html);
        $this->assertStringContainsString('height: 120px', $html);
    }
}
