<?php

namespace App\Builder\Services;

use Vertex\Forms\Services\FormService;
use Vertex\Forms\Models\Form;
use App\Models\Page;
use App\Theme\Services\ThemeManager;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class PageRenderer
{
    public function __construct(
        private readonly ThemeManager $themes,
        private readonly FormService $formService,
    ) {
    }

    public function render(?array $content, bool $editor = false): HtmlString
    {
        $sections = $content['sections'] ?? [];

        if (! is_array($sections) || $sections === []) {
            return new HtmlString('');
        }

        $html = '';

        foreach ($sections as $index => $section) {
            if (! is_array($section)) {
                continue;
            }

            $html .= $this->renderSection($section, (int) $index, $editor);
        }

        return new HtmlString($html);
    }

    private function renderSection(array $section, int $index = 0, bool $editor = false): string
    {
        $blocks = $section['blocks'] ?? $section['children'] ?? [];
        $blocks = is_array($blocks) ? $blocks : [];
        $sectionSettings = is_array($section['settings'] ?? null) ? $section['settings'] : [];
        $style = $this->style([
            'background-color' => $sectionSettings['background_color'] ?? $section['background_color'] ?? null,
            'padding-top' => $this->size($sectionSettings['padding_top'] ?? $section['padding_top'] ?? null),
            'padding-bottom' => $this->size($sectionSettings['padding_bottom'] ?? $section['padding_bottom'] ?? null),
        ]);
        $class = trim('vc-section '.($sectionSettings['css_class'] ?? $section['css_class'] ?? ''));
        $attributes = $editor
            ? ' data-vc-section-id="'.e($section['id'] ?? '').'" data-vc-section-index="'.e((string) $index).'"'
            : '';

        return '<section class="'.e($class).'"'.$attributes.$style.'><div class="vc-container">'
            .$this->renderBlocks($blocks, $editor, 0)
            .'</div></section>';
    }

    private function renderBlock(array $block, bool $editor = false, ?int $blockIndex = null, int $depth = 0): string
    {
        $type = Str::of($block['type'] ?? 'unknown')->lower()->value();
        $settings = $block['settings'] ?? $block;
        $override = $this->themes->blockView($type);
        $html = null;

        if ($override) {
            $html = match ($type) {
                'html' => $this->html($settings),
                'form' => $this->form($settings),
                default => null,
            };

            $html = view($override, array_merge($settings, [
                'block' => $block,
                'settings' => $settings,
                'html' => $html,
            ]))->render();
        } else {
             $html = match ($type) {
                 'heading' => $this->heading($settings),
                 'text' => $this->text($settings),
                 'button' => $this->button($settings),
                 'divider' => $this->divider($settings),
                 'faq' => $this->faq($settings),
                 'list' => $this->list($settings),
                 'image' => $this->image($settings),
                 'video' => $this->video($settings),
                 'html' => $this->html($settings),
                 'form' => $this->form($settings),
                 'hero' => $this->hero($settings),
                 'gallery' => $this->gallery($settings),
                 'columns' => $this->columns($settings, $editor, $depth + 1),
                 'container' => $this->container($settings, $editor, $depth + 1),
                 'cart' => $this->cart($settings),
                 'icon' => $this->icon($settings),
                 'counter' => $this->counter($settings),
                 'progress-bar' => $this->progressBar($settings),
                 'alert' => $this->alert($settings),
                 'breadcrumbs' => $this->breadcrumbs($settings),
                 'accordion' => $this->accordion($settings),
                 'tabs' => $this->tabs($settings),
                 'collapse' => $this->collapse($settings),
                 'modal' => $this->modal($settings),
                 'tooltip' => $this->tooltip($settings),
                 'testimonials' => $this->testimonials($settings),
                 'pricing-table' => $this->pricingTable($settings),
                 'product-card' => $this->productCard($settings),
                 'product-list' => $this->productList($settings),
                 'news-feed' => $this->newsFeed($settings),
                 'seo-meta' => $this->seoMeta($settings),
                 'breakdance-rich-text' => $this->text($settings),
                 'breakdance-icon-list' => $this->iconList($settings),
                 'breakdance-social-icons' => $this->socialIcons($settings),
                 'breakdance-logo-list' => $this->logoList($settings),
                 'breakdance-search-form' => $this->searchForm($settings),
                 'spacer' => '<div class="vc-spacer" style="height: '.e($this->size($settings['height'] ?? 32)).';"></div>',
                 default => '<!-- Unknown VertexCMS block: '.e($type).' -->',
              };
        }

        if (! $editor) {
            return $html;
        }

        return '<div class="vc-live-block" data-vc-block-id="'.e($block['id'] ?? '').'" data-vc-block-index="'.e((string) ($blockIndex ?? '')).'" data-vc-block-depth="'.e((string) $depth).'" data-vc-block-type="'.e($type).'">'.$html.'</div>';
    }

    private function heading(array $settings): string
    {
        $level = (string) ($settings['level'] ?? 'h2');
        $level = in_array($level, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true) ? $level : 'h2';

        return sprintf(
            '<%1$s class="vc-heading" style="%2$s">%3$s</%1$s>',
            $level,
            e($this->inlineStyle([
                'color' => $settings['color'] ?? null,
                'text-align' => $settings['align'] ?? null,
                'font-size' => $this->size($settings['font_size'] ?? null),
                'font-weight' => $settings['font_weight'] ?? null,
            ])),
            e($settings['text'] ?? ''),
        );
    }

    private function text(array $settings): string
    {
        return '<div class="vc-text" style="'.e($this->inlineStyle([
            'color' => $settings['color'] ?? null,
            'text-align' => $settings['align'] ?? null,
            'font-size' => $this->size($settings['font_size'] ?? null),
        ])).'">'.nl2br(e($settings['text'] ?? $settings['content'] ?? '')).'</div>';
    }

    private function button(array $settings): string
    {
        return sprintf(
            '<p class="vc-button-wrap"><a class="vc-button vc-button-%s" href="%s" target="%s">%s</a></p>',
            e($settings['style'] ?? 'primary'),
            e($settings['url'] ?? '#'),
            e($settings['target'] ?? '_self'),
            e($settings['text'] ?? 'Подробнее'),
        );
    }

    private function faq(array $settings): string
    {
        $items = $settings['items'] ?? [];

        $html = '';

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $html .= '<details class="vc-faq-item"><summary>'.e($item['question'] ?? '').'</summary><div>'
                .nl2br(e($item['answer'] ?? '')).'</div></details>';
        }

        return '<div class="vc-faq">'.$html.'</div>';
    }

    private function list(array $settings): string
    {
        $type = $settings['type'] ?? 'disc';
        $tag = $type === 'decimal' ? 'ol' : 'ul';
        $class = match ($type) {
            'decimal' => 'vc-list-decimal',
            'none' => 'vc-list-none',
            default => 'vc-list-disc',
        };
        $items = is_array($settings['items'] ?? null) ? $settings['items'] : [];

        $html = '';

        foreach ($items as $item) {
            $content = is_array($item) ? ($item['content'] ?? '') : $item;

            $html .= '<li class="vc-list-item">'.e($content).'</li>';
        }

        return '<'.$tag.' class="vc-list '.$class.'">'.$html.'</'.$tag.'>';
    }

    private function image(array $settings): string
    {
        $url = $settings['url'] ?? '';

        if ($url === '' && filled($settings['media_id'] ?? null)) {
            $url = '/api/media/'.e($settings['media_id']);
        }

        if ($url === '') {
            return '<div class="vc-media-placeholder vc-image-placeholder">Image placeholder</div>';
        }

        return '<img class="vc-image" src="'.e($url).'" alt="'.e($settings['alt'] ?? '').'" style="'.e($this->inlineStyle([
            'height' => $this->size($settings['height'] ?? null),
            'width' => $this->size($settings['width'] ?? null),
            'object-fit' => $settings['object_fit'] ?? null,
        ])).'">';
    }

    private function video(array $settings): string
    {
        $type = (string) ($settings['type'] ?? 'youtube');
        $url = trim((string) ($settings['url'] ?? ''));
        $ratio = (string) ($settings['ratio'] ?? '16:9');
        $ratio = preg_match('/^\d{1,2}:\d{1,2}$/', $ratio) === 1 ? str_replace(':', ' / ', $ratio) : '16 / 9';
        $style = $this->inlineStyle(['aspect-ratio' => $ratio]);
        $autoplay = filter_var($settings['autoplay'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $loop = filter_var($settings['loop'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $muted = filter_var($settings['muted'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $controls = filter_var($settings['controls'] ?? true, FILTER_VALIDATE_BOOLEAN);

        $videoId = match ($type) {
            'youtube' => $this->youtubeId($url),
            'vimeo' => $this->vimeoId($url),
            default => null,
        };

        if ($videoId !== null) {
            $host = $type === 'vimeo' ? 'https://player.vimeo.com/video/' : 'https://www.youtube.com/embed/';
            $params = array_filter([
                'autoplay' => $autoplay ? '1' : null,
                ($type === 'vimeo' ? 'muted' : 'mute') => $muted ? '1' : null,
                'loop' => $loop ? '1' : null,
                'playlist' => $loop && $type === 'youtube' ? $videoId : null,
                'rel' => '0',
            ], static fn ($value) => $value !== null);
            $query = $params === [] ? '' : '?'.http_build_query($params);
            $pointer = $controls ? '' : ' style="pointer-events: none;"';

            return '<div class="vc-video vc-video-'.$type.'" style="'.e($style).'"><iframe src="'.e($host.$videoId.$query)
                .'" loading="lazy" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen'.$pointer.'></iframe></div>';
        }

        if ($type === 'html5' && $this->isSafeMediaUrl($url)) {
            $attributes = $autoplay ? ' autoplay'.($muted ? ' muted' : '') : ($muted ? ' muted' : '');
            $attributes .= $loop ? ' loop' : '';
            $attributes .= $controls ? ' controls' : '';

            return '<div class="vc-video vc-video-html5" style="'.e($style).'"><video src="'.e($url).'" playsinline preload="metadata"'.$attributes.'></video></div>';
        }

        return '<div class="vc-media-placeholder vc-video-placeholder">Video placeholder</div>';
    }

    private function divider(array $settings): string
    {
        $style = (string) ($settings['style'] ?? 'solid');
        $style = in_array($style, ['solid', 'dashed', 'dotted', 'double'], true) ? $style : 'solid';
        $color = (string) ($settings['color'] ?? '#e2e8f0');
        $color = preg_match('/^#(?:[0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $color) === 1 ? $color : '#e2e8f0';
        $thickness = max(1, min(20, (int) ($settings['thickness'] ?? 1)));
        $width = (string) ($settings['width'] ?? '100%');
        $width = preg_match('/^\d{1,3}(?:\.\d{1,2})?(?:px|%)$/', $width) === 1 ? $width : '100%';
        $declarations = "border: 0; border-top: {$thickness}px {$style} {$color}; width: {$width};";

        return '<div class="vc-divider-wrap"><hr class="vc-divider vc-divider-'.$style.'" style="'.e($declarations).'"></div>';
    }

    private function youtubeId(string $url): ?string
    {
        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})~', $url, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }

    private function vimeoId(string $url): ?string
    {
        if (preg_match('~vimeo\.com/(?:video/)?(\d{4,})~', $url, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }

    private function isSafeMediaUrl(string $url): bool
    {
        return preg_match('~^(https?:)?//~i', $url) === 1 || str_starts_with($url, '/');
    }

    private function html(array $settings): string
    {
        $html = (string) ($settings['html'] ?? '');

        // Drop raw execution containers together with their contents so script
        // and style bodies never leak into the rendered markup as text.
        $html = preg_replace('#<\s*(script|style|iframe|object|embed|noscript)[^>]*>.*?<\s*/\s*\1\s*>#is', '', $html) ?? $html;
        $html = strip_tags($html, '<p><br><strong><b><em><i><ul><ol><li><a><span><div>');
        $html = preg_replace('/\son\w+=(["\']).*?\1/i', '', $html) ?? $html;

        return str_ireplace(['javascript:', 'data:', 'srcdoc='], '', $html);
    }

     /**
      * Render form block on frontend
      */
     private function form(array $settings): string
     {
         $formId = $settings['form_id'] ?? null;
         $form = null;

         // Try to get existing form by ID
         if ($formId) {
             $form = Form::query()->find($formId);
         }

         // If no form found, render placeholder
         if (!$form) {
             return '<div class="vc-form-placeholder">[Форма не найдена]</div>';
         }

         // Render form Vue component with data attributes
         $formConfig = $this->formService->renderForm($form);
         $actionUrl = route('public.forms.submit', $form->slug);
         $nonce = csrf_token();
         $uniqueId = 'form_'.$form->id.'_'.Str::random(8);

         return view('forms::blocks.form', [
             'form' => $form,
             'formConfig' => $formConfig,
             'actionUrl' => $actionUrl,
             'nonce' => $nonce,
             'uniqueId' => $uniqueId,
             'settings' => $settings,
         ])->render();
     }

     /**
      * Render hero block on frontend
      */
     private function hero(array $settings): string
     {
         $background = $settings['background'] ?? '';
         $title = e($settings['title'] ?? '');
         $subtitle = e($settings['subtitle'] ?? '');
         $buttonText = e($settings['button_text'] ?? '');
         $buttonUrl = e($settings['button_url'] ?? '#');
         $buttonTarget = e($settings['button_target'] ?? '_self');
         $buttonBgColor = e($settings['button_bg_color'] ?? '#3b82f6');
         $buttonTextColor = e($settings['button_text_color'] ?? '#ffffff');
         $buttonBorderColor = e($settings['button_border_color'] ?? 'transparent');
         $titleColor = e($settings['title_color'] ?? '#ffffff');
         $subtitleColor = e($settings['subtitle_color'] ?? '#ffffff');
         $paddingTop = $settings['padding_top'] ?? 80;
         $paddingBottom = $settings['padding_bottom'] ?? 80;

         $style = '';
         if ($background) {
             $style = "background-image: url('$background'); background-size: cover; background-position: center;";
         }

         $titleHtml = $title !== ''
             ? "<h1 class='vc-hero-title' style='color: {$titleColor}; margin-bottom: 0.5rem;'>{$title}</h1>"
             : '';
         $subtitleHtml = $subtitle !== ''
             ? "<p class='vc-hero-subtitle' style='color: {$subtitleColor}; margin-bottom: 1.5rem;'>{$subtitle}</p>"
             : '';
         $buttonHtml = $buttonText !== ''
             ? "<a href='{$buttonUrl}' target='{$buttonTarget}' class='vc-hero-button' style='display: inline-block; background-color: {$buttonBgColor}; color: {$buttonTextColor}; border: 2px solid {$buttonBorderColor}; padding: 0.75rem 1.5rem; text-decoration: none; font-weight: 500; border-radius: 0.375rem;'>{$buttonText}</a>"
             : '';

         return <<<HTML
<section class="vc-hero" style="{$style} padding-top: {$paddingTop}px; padding-bottom: {$paddingBottom}px; text-align: center; color: white;">
    <div class="vc-hero-content">
        {$titleHtml}
        {$subtitleHtml}
        {$buttonHtml}
    </div>
</section>
HTML;
     }

/**
        * Render gallery block on frontend
        */
      private function gallery(array $settings): string
      {
          return view('builder.blocks.gallery', [
              'settings' => $settings,
          ])->render();
      }

    private function columns(array $settings, bool $editor = false, int $depth = 0): string
    {
        $count = max(1, min(4, (int) ($settings['count'] ?? count($settings['columns'] ?? []) ?: 2)));
        $gap = $this->gap($settings['gap'] ?? 'md');
        $columns = is_array($settings['columns'] ?? null)
            ? $settings['columns']
            : array_fill(0, $count, ['blocks' => []]);
        $style = $this->inlineStyle([
            'display' => 'grid',
            'gap' => $gap,
            'grid-template-columns' => "repeat({$count}, minmax(0, 1fr))",
        ]);

        $html = '';

        foreach ($columns as $column) {
            if (! is_array($column)) {
                continue;
            }

            $blocks = is_array($column['blocks'] ?? null) ? $column['blocks'] : [];

            $html .= '<div class="vc-column">'.$this->renderBlocks($blocks, $editor, $depth).'</div>';
        }

        return '<div class="vc-columns" style="'.e($style).'">'.$html.'</div>';
    }

    private function container(array $settings, bool $editor = false, int $depth = 0): string
    {
        $padding = is_array($settings['padding'] ?? null) ? $settings['padding'] : [];
        $style = $this->inlineStyle([
            'max-width' => $this->maxWidth($settings['max_width'] ?? '7xl'),
            'padding-top' => $this->size($settings['padding_top'] ?? $padding['top'] ?? 16),
            'padding-bottom' => $this->size($settings['padding_bottom'] ?? $padding['bottom'] ?? 16),
            'padding-left' => $this->size($settings['padding_left'] ?? $padding['left'] ?? 4),
            'padding-right' => $this->size($settings['padding_right'] ?? $padding['right'] ?? 4),
        ]);
        $blocks = is_array($settings['blocks'] ?? null) ? $settings['blocks'] : [];

        return '<div class="vc-container-block" style="'.e($style).'">'.$this->renderBlocks($blocks, $editor, $depth).'</div>';
    }

    private function cart(array $settings): string
    {
        return view('builder.blocks.cart', [
            'settings' => $settings,
        ])->render();
    }

    private function iconList(array $settings): string
    {
        $icon = e((string) ($settings['icon'] ?? 'check'));
        $color = e((string) ($settings['color'] ?? '#10b981'));
        $items = is_array($settings['items'] ?? null) ? $settings['items'] : [];
        $html = '';

        foreach ($items as $item) {
            $content = e((string) ($item['content'] ?? ''));
            $html .= '<li class="vc-icon-list-item" style="display:flex;gap:0.75rem;align-items:flex-start;"><span class="vc-icon-list-mark" style="color: '.$color.';font-weight:700;">'.$icon.'</span><span>'.$content.'</span></li>';
        }

        return '<ul class="vc-icon-list" style="display:grid;gap:0.75rem;">'.$html.'</ul>';
    }

    private function socialIcons(array $settings): string
    {
        $items = is_array($settings['items'] ?? null) ? $settings['items'] : [];
        $iconColor = e((string) ($settings['icon_color'] ?? '#111827'));
        $backgroundColor = e((string) ($settings['background_color'] ?? '#f3f4f6'));
        $html = '';

        foreach ($items as $item) {
            $platform = e(ucfirst((string) ($item['platform'] ?? 'social')));
            $url = e((string) ($item['url'] ?? '#'));
            $html .= '<a class="vc-social-icon" href="'.$url.'" style="display:inline-flex;align-items:center;justify-content:center;padding:0.65rem 0.9rem;border-radius:999px;background:'.$backgroundColor.';color:'.$iconColor.';text-decoration:none;font-weight:600;">'.$platform.'</a>';
        }

        return '<div class="vc-social-icons" style="display:flex;flex-wrap:wrap;gap:0.75rem;">'.$html.'</div>';
    }

    private function logoList(array $settings): string
    {
        $logos = is_array($settings['logos'] ?? null) ? $settings['logos'] : [];
        $columns = max(2, min(6, (int) ($settings['columns'] ?? 4)));
        $html = '';

        foreach ($logos as $logo) {
            $image = $logo['image'] ?? null;
            $src = is_array($image) ? ($image['url'] ?? '') : (string) $image;
            $alt = e((string) ($logo['alt'] ?? 'Logo'));
            $url = e((string) ($logo['url'] ?? '#'));
            $content = $src !== ''
                ? '<img src="'.e($src).'" alt="'.$alt.'" style="max-height:48px;max-width:100%;object-fit:contain;">'
                : '<span style="font-size:0.875rem;color:#64748b;">Logo</span>';
            $html .= '<a href="'.$url.'" class="vc-logo-item" style="display:flex;align-items:center;justify-content:center;min-height:96px;padding:1rem;border:1px solid #e2e8f0;border-radius:1rem;background:#fff;">'.$content.'</a>';
        }

        return '<div class="vc-logo-list" style="display:grid;grid-template-columns:repeat('.$columns.', minmax(0, 1fr));gap:1rem;">'.$html.'</div>';
    }

    private function searchForm(array $settings): string
    {
        $placeholder = e((string) ($settings['placeholder'] ?? 'Search content'));
        $buttonText = e((string) ($settings['button_text'] ?? 'Search'));
        $actionUrl = e((string) ($settings['action_url'] ?? '/search'));

        return '<form class="vc-search-form" action="'.$actionUrl.'" method="get" style="display:flex;gap:0.75rem;"><input type="search" name="q" placeholder="'.$placeholder.'" style="flex:1;min-width:0;padding:0.8rem 1rem;border:1px solid #cbd5e1;border-radius:0.9rem;"><button type="submit" style="padding:0.8rem 1rem;border-radius:0.9rem;background:#0f172a;color:#fff;font-weight:600;">'.$buttonText.'</button></form>';
    }

    private function renderBlocks(array $blocks, bool $editor = false, int $depth = 0): string
    {
        $html = '';

        foreach ($blocks as $blockIndex => $block) {
            if (! is_array($block)) {
                continue;
            }

            $html .= $this->renderBlock($block, $editor, (int) $blockIndex, $depth);
        }

        return $html;
    }

    private function iconSvg(string $name): string
    {
        $icons = [
            'star' => '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>',
            'heart' => '<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>',
            'check' => '<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>',
            'x' => '<path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>',
            'arrow' => '<path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"/>',
            'dots' => '<circle cx="12" cy="12" r="2"/><circle cx="12" cy="5" r="2"/><circle cx="12" cy="19" r="2"/>',
        ];

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">'
            .($icons[$name] ?? $icons['star']).'</svg>';
    }

    private function icon(array $settings): string
    {
        $name = in_array($settings['icon'] ?? 'star', ['star', 'heart', 'check', 'x', 'arrow', 'dots'], true)
            ? $settings['icon']
            : 'star';
        $size = in_array($settings['size'] ?? 'md', ['sm', 'md', 'lg', 'xl'], true) ? $settings['size'] : 'md';
        $radius = in_array($settings['radius'] ?? 'none', ['none', 'sm', 'md', 'lg', 'full'], true)
            ? $settings['radius']
            : 'none';
        $style = $this->inlineStyle([
            'color' => $settings['color'] ?? null,
            'background-color' => $settings['background'] ?? null,
        ]);

        return '<span class="vc-icon vc-icon-'.$size.' vc-icon-radius-'.$radius.'" style="'.e($style).'">'
            .$this->iconSvg((string) $name).'</span>';
    }

    private function counter(array $settings): string
    {
        $raw = $settings['value'] ?? 0;
        $value = is_numeric($raw) ? (float) $raw : 0.0;
        $display = floor($value) === $value ? (string) (int) $value : rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
        $duration = max(0, min(5000, (int) ($settings['duration'] ?? 2000)));
        $label = (string) ($settings['label'] ?? '');

        $html = '<div class="vc-counter" data-vc-counter data-value="'.e($display).'" data-duration="'.e((string) $duration).'">'
            .'<span class="vc-counter-prefix">'.e((string) ($settings['prefix'] ?? '')).'</span>'
            .'<span class="vc-counter-value" data-vc-counter-value>'.e($display).'</span>'
            .'<span class="vc-counter-suffix">'.e((string) ($settings['suffix'] ?? '')).'</span>'
            .'</div>';

        if ($label !== '') {
            $html .= '<div class="vc-counter-label">'.e($label).'</div>';
        }

        return $html;
    }

    private function progressBar(array $settings): string
    {
        $value = is_numeric($settings['value'] ?? null) ? (float) $settings['value'] : 0.0;
        $max = is_numeric($settings['max'] ?? null) ? (float) $settings['max'] : 100.0;
        $max = $max > 0 ? $max : 100.0;
        $percentage = min(100, max(0, ($value / $max) * 100));
        $height = max(2, min(50, (int) ($settings['height'] ?? 8)));
        $color = (string) ($settings['color'] ?? '#3b82f6');
        $showLabel = filter_var($settings['show_label'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;

        $html = '<div class="vc-progress-bar">';

        if ($showLabel) {
            $html .= '<div class="vc-progress-meta"><span>'.e(number_format($value)).' / '.e(number_format($max))
                .'</span><span>'.e(number_format($percentage, 1)).'%</span></div>';
        }

        $html .= '<div class="vc-progress-track" style="height: '.e((string) $height).'px;" role="progressbar"'
            .' aria-valuenow="'.e((string) round($percentage, 1)).'" aria-valuemin="0" aria-valuemax="100">'
            .'<div class="vc-progress-fill" style="width: '.e((string) round($percentage, 2)).'%; background-color: '.e($color).';"></div>'
            .'</div></div>';

        return $html;
    }

    private function alert(array $settings): string
    {
        $type = in_array($settings['type'] ?? 'info', ['info', 'success', 'warning', 'error'], true)
            ? $settings['type']
            : 'info';
        $closable = filter_var($settings['closable'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
        $title = (string) ($settings['title'] ?? '');

        $html = '<div class="vc-alert vc-alert-'.$type.'" role="alert" data-vc-alert>';

        if ($title !== '') {
            $html .= '<strong class="vc-alert-title">'.e($title).'</strong>';
        }

        $html .= '<span class="vc-alert-content">'.nl2br(e((string) ($settings['content'] ?? ''))).'</span>';

        if ($closable) {
            $html .= '<button class="vc-alert-close" type="button" data-vc-alert-close aria-label="'.e('Close').'">&times;</button>';
        }

        return $html.'</div>';
    }

    private function breadcrumbs(array $settings): string
    {
        $items = is_array($settings['items'] ?? null) ? $settings['items'] : [];
        $separator = (string) ($settings['separator'] ?? '/');
        $items = array_values(array_filter($items, fn ($item) => is_array($item) && filled($item['title'] ?? null)));

        if ($items === []) {
            return '';
        }

        $last = count($items) - 1;
        $html = '';

        foreach ($items as $index => $item) {
            $title = e((string) ($item['title'] ?? ''));
            $url = $item['url'] ?? null;
            $isLast = $index === $last;

            $content = (! $isLast && filled($url))
                ? '<a href="'.e((string) $url).'">'.$title.'</a>'
                : '<span aria-current="'.($isLast ? 'page' : 'false').'">'.$title.'</span>';

            if ($index > 0) {
                $html .= '<span class="vc-breadcrumbs-separator" aria-hidden="true">'.e($separator).'</span>';
            }

            $html .= $content;
        }

        return '<nav class="vc-breadcrumbs" aria-label="'.e('Breadcrumb').'">'.$html.'</nav>';
    }

    private function accordion(array $settings): string
    {
        $items = is_array($settings['items'] ?? null) ? $settings['items'] : [];
        $allowMultiple = filter_var($settings['allow_multiple'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
        $html = '';

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $open = filter_var($item['open'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;

            $html .= '<details class="vc-accordion-item"'.($open ? ' open' : '').'>'
                .'<summary>'.e((string) ($item['title'] ?? '')).'</summary>'
                .'<div class="vc-accordion-body">'.nl2br(e((string) ($item['content'] ?? ''))).'</div>'
                .'</details>';
        }

        return $html === '' ? '' : '<div class="vc-accordion" data-vc-accordion data-allow-multiple="'.($allowMultiple ? 'true' : 'false').'">'.$html.'</div>';
    }

    private function tabs(array $settings): string
    {
        $tabs = is_array($settings['tabs'] ?? null) ? array_values(array_filter($settings['tabs'], 'is_array')) : [];

        if ($tabs === []) {
            return '';
        }

        $style = (string) ($settings['style'] ?? 'line');
        $style = in_array($style, ['primary', 'secondary', 'outline', 'ghost', 'solid', 'dashed', 'dotted', 'double', 'line', 'boxed', 'pill'], true)
            ? $style
            : 'line';
        $alignment = (string) ($settings['alignment'] ?? 'left');
        $alignment = in_array($alignment, ['left', 'center', 'right'], true)
            ? $alignment
            : 'left';
        $nav = '';
        $panels = '';

        foreach ($tabs as $index => $tab) {
            $nav .= '<button class="vc-tab'.($index === 0 ? ' is-active' : '').'" type="button" role="tab"'
                .' data-vc-tab="'.$index.'" aria-selected="'.($index === 0 ? 'true' : 'false').'">'
                .e((string) ($tab['title'] ?? '')).'</button>';
            $panels .= '<div class="vc-tab-panel'.($index === 0 ? ' is-active' : '').'" role="tabpanel"'
                .' data-vc-tab-panel="'.$index.'">'.nl2br(e((string) ($tab['content'] ?? ''))).'</div>';
        }

        return '<div class="vc-tabs vc-tabs-'.$style.' vc-tabs-'.$alignment.'" data-vc-tabs>'
            .'<div class="vc-tabs-nav" role="tablist">'.$nav.'</div>'
            .'<div class="vc-tabs-panels">'.$panels.'</div></div>';
    }

    private function collapse(array $settings): string
    {
        $open = filter_var($settings['open'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;

        return '<details class="vc-collapse"'.($open ? ' open' : '').'>'
            .'<summary>'.e((string) ($settings['title'] ?? '')).'</summary>'
            .'<div class="vc-collapse-body">'.nl2br(e((string) ($settings['content'] ?? ''))).'</div>'
            .'</details>';
    }

    private function modal(array $settings): string
    {
        $size = in_array($settings['size'] ?? 'md', ['sm', 'md', 'lg', 'xl'], true) ? $settings['size'] : 'md';
        $modalId = 'vc-modal-'.Str::random(8);
        $title = (string) ($settings['title'] ?? '');

        $html = '<div class="vc-modal-block">'
            .'<button class="vc-button vc-button-primary" type="button" data-vc-modal-open="'.e($modalId).'">'
            .e((string) ($settings['trigger_text'] ?? 'Open modal')).'</button>'
            .'<dialog class="vc-modal vc-modal-'.$size.'" id="'.e($modalId).'" data-vc-modal>'
            .'<div class="vc-modal-header">';

        if ($title !== '') {
            $html .= '<h3 class="vc-modal-title">'.e($title).'</h3>';
        }

        $html .= '<button class="vc-modal-close" type="button" data-vc-modal-close aria-label="'.e('Close').'">&times;</button>'
            .'</div><div class="vc-modal-body">'.nl2br(e((string) ($settings['content'] ?? ''))).'</div></dialog></div>';

        return $html;
    }

    private function tooltip(array $settings): string
    {
        $position = (string) ($settings['position'] ?? 'top');
        $position = in_array($position, ['top', 'bottom', 'left', 'right'], true) ? $position : 'top';

        return '<span class="vc-tooltip" tabindex="0">'.e((string) ($settings['text'] ?? ''))
            .'<span class="vc-tooltip-bubble vc-tooltip-'.$position.'" role="tooltip">'
            .e((string) ($settings['content'] ?? '')).'</span></span>';
    }

    private function testimonials(array $settings): string
    {
        $items = is_array($settings['testimonials'] ?? null) ? $settings['testimonials'] : [];
        $showRating = filter_var($settings['show_rating'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
        $layout = (string) ($settings['layout'] ?? 'grid');
        $layout = in_array($layout, ['grid', 'cards', 'minimal', 'list'], true) ? $layout : 'grid';
        $html = '';

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $rating = min(5, max(0, (int) round((float) ($item['rating'] ?? 0))));
            $avatar = $this->mediaUrl($item['avatar'] ?? null);
            $stars = '';

            if ($showRating && $rating > 0) {
                $stars = '<div class="vc-testimonial-rating" aria-label="'.e($rating.'/5').'">'
                    .str_repeat('<span class="vc-star is-active">&#9733;</span>', $rating)
                    .str_repeat('<span class="vc-star">&#9733;</span>', 5 - $rating).'</div>';
            }

            $html .= '<figure class="vc-testimonial">'
                .($avatar !== '' ? '<img class="vc-testimonial-avatar" src="'.e($avatar).'" alt="'.e((string) ($item['author'] ?? '')).'" loading="lazy">' : '')
                .$stars
                .'<blockquote class="vc-testimonial-text">'.e((string) ($item['text'] ?? '')).'</blockquote>'
                .'<figcaption class="vc-testimonial-author"><cite>'.e((string) ($item['author'] ?? '')).'</cite>'
                .(filled($item['role'] ?? null) ? '<span class="vc-testimonial-role">'.e((string) $item['role']).'</span>' : '')
                .'</figcaption></figure>';
        }

        return $html === '' ? '' : '<div class="vc-testimonials vc-testimonials-'.$layout.'">'.$html.'</div>';
    }

    private function pricingTable(array $settings): string
    {
        $plans = is_array($settings['plans'] ?? null) ? $settings['plans'] : [];
        $columns = max(1, min(6, (int) ($settings['columns'] ?? 3)));
        $html = '';

        foreach ($plans as $plan) {
            if (! is_array($plan)) {
                continue;
            }

            $features = is_array($plan['features'] ?? null) ? $plan['features'] : [];
            $highlighted = filter_var($plan['highlighted'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;

            $html .= '<div class="vc-pricing-plan'.($highlighted ? ' is-highlighted' : '').'">';

            if ($highlighted) {
                $html .= '<span class="vc-pricing-badge">'.e((string) ($plan['badge'] ?? 'Popular')).'</span>';
            }

            $html .= '<h3 class="vc-pricing-name">'.e((string) ($plan['name'] ?? '')).'</h3>'
                .'<div class="vc-pricing-price"><span class="vc-pricing-currency">'.e((string) ($plan['currency'] ?? '$')).'</span>'
                .'<span class="vc-pricing-amount">'.e((string) ($plan['price'] ?? '0')).'</span>';

            if (filled($plan['period'] ?? null)) {
                $html .= '<span class="vc-pricing-period">/'.e((string) $plan['period']).'</span>';
            }

            $html .= '</div>';

            if ($features !== []) {
                $html .= '<ul class="vc-pricing-features">';

                foreach ($features as $feature) {
                    $html .= '<li>'.e((string) $feature).'</li>';
                }

                $html .= '</ul>';
            }

            $html .= '<a class="vc-button vc-button-primary vc-pricing-button" href="'.e((string) ($plan['button_url'] ?? '#')).'">'
                .e((string) ($plan['button_text'] ?? 'Choose plan')).'</a></div>';
        }

        return $html === '' ? '' : '<div class="vc-pricing" style="--vc-pricing-columns: '.$columns.';">'.$html.'</div>';
    }

    private function productCard(array $settings): string
    {
        $title = (string) ($settings['title'] ?? '');
        $image = $this->mediaUrl($settings['image'] ?? $settings['image_url'] ?? null);
        $currency = (string) ($settings['currency'] ?? '$');
        $price = is_numeric($settings['price'] ?? null) ? (float) $settings['price'] : 0.0;
        $oldPrice = is_numeric($settings['old_price'] ?? null) ? (float) $settings['old_price'] : null;
        $rating = min(5, max(0, (int) round((float) ($settings['rating'] ?? 0))));
        $reviewsCount = (int) ($settings['reviews_count'] ?? 0);

        $html = '<article class="vc-product-card">';

        if ($image !== '') {
            $html .= '<div class="vc-product-media"><img src="'.e($image).'" alt="'.e($title).'" loading="lazy"></div>';
        }

        $html .= '<div class="vc-product-body"><h3 class="vc-product-title">'.e($title).'</h3>';

        if (filled($settings['description'] ?? null)) {
            $html .= '<p class="vc-product-description">'.e(Str::limit((string) $settings['description'], 140)).'</p>';
        }

        if ($rating > 0) {
            $html .= '<div class="vc-product-rating">'.str_repeat('<span class="vc-star is-active">&#9733;</span>', $rating)
                .str_repeat('<span class="vc-star">&#9733;</span>', 5 - $rating)
                .($reviewsCount > 0 ? '<span class="vc-product-reviews">('.e((string) $reviewsCount).')</span>' : '').'</div>';
        }

        $html .= '<div class="vc-product-price">';

        if ($oldPrice !== null && $oldPrice > $price) {
            $html .= '<s class="vc-product-old-price">'.e(number_format($oldPrice, 2)).' '.e($currency).'</s>';
        }

        $html .= '<span class="vc-product-current-price">'.e(number_format($price, 2)).' '.e($currency).'</span></div>'
            .'<button class="vc-button vc-button-primary vc-product-button" type="button">'
            .e((string) ($settings['button_text'] ?? 'Add to cart')).'</button></div></article>';

        return $html;
    }

    private function productList(array $settings): string
    {
        $products = is_array($settings['products'] ?? null) ? $settings['products'] : [];
        $columns = max(1, min(6, (int) ($settings['columns'] ?? 4)));
        $layout = (string) ($settings['layout'] ?? 'grid');
        $layout = in_array($layout, ['grid', 'list', 'minimal'], true) ? $layout : 'grid';
        $showPrice = filter_var($settings['show_price'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
        $showRating = filter_var($settings['show_rating'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
        $html = '';

        foreach ($products as $product) {
            if (! is_array($product)) {
                continue;
            }

            $image = $this->mediaUrl($product['image'] ?? null);
            $rating = min(5, max(0, (int) round((float) ($product['rating'] ?? 0))));

            $html .= '<article class="vc-product-card">';

            if ($image !== '') {
                $html .= '<div class="vc-product-media"><img src="'.e($image).'" alt="'.e((string) ($product['title'] ?? '')).'" loading="lazy"></div>';
            }

            $html .= '<div class="vc-product-body"><h3 class="vc-product-title">'.e((string) ($product['title'] ?? '')).'</h3>';

            if ($showRating && $rating > 0) {
                $html .= '<div class="vc-product-rating">'.str_repeat('<span class="vc-star is-active">&#9733;</span>', $rating)
                    .str_repeat('<span class="vc-star">&#9733;</span>', 5 - $rating).'</div>';
            }

            if ($showPrice && is_numeric($product['price'] ?? null)) {
                $html .= '<div class="vc-product-price"><span class="vc-product-current-price">'
                    .e(number_format((float) $product['price'], 2)).'</span></div>';
            }

            $html .= '</div></article>';
        }

        return $html === '' ? '' : '<div class="vc-product-list vc-product-list-'.$layout.'" data-vc-product-list'
            .' style="--vc-product-columns: '.$columns.';">'.$html.'</div>';
    }

    private function newsFeed(array $settings): string
    {
        $limit = max(1, min(50, (int) ($settings['count'] ?? 3)));
        $columns = max(1, min(6, (int) ($settings['columns'] ?? 3)));
        $layout = (string) ($settings['layout'] ?? 'grid');
        $layout = in_array($layout, ['grid', 'list', 'minimal'], true) ? $layout : 'grid';
        $category = (string) ($settings['category'] ?? 'all');
        $showDate = filter_var($settings['show_date'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
        $showExcerpt = filter_var($settings['show_excerpt'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;

        $query = Page::query()
            ->with('seoMeta')
            ->where('status', 'published')
            ->where(fn ($builder) => $builder->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->latest('published_at');

        if ($category !== '' && $category !== 'all') {
            $query->whereHas('terms', fn ($terms) => $terms->where('terms.slug', $category));
        }

        $pages = $query->limit($limit)->get();
        $html = '';

        foreach ($pages as $page) {
            $url = $page->getPublicUrl() ?? '#';
            $description = (string) ($page->seoMeta->description ?? '');

            $html .= '<article class="vc-news-item">';

            if ($showDate && $page->published_at) {
                $html .= '<time class="vc-news-date" datetime="'.e($page->published_at->toIso8601String()).'">'
                    .e($page->published_at->format('d.m.Y')).'</time>';
            }

            $html .= '<h3 class="vc-news-title"><a href="'.e($url).'">'.e((string) $page->title).'</a></h3>';

            if ($showExcerpt && $description !== '') {
                $html .= '<p class="vc-news-excerpt">'.e(Str::limit($description, 160)).'</p>';
            }

            $html .= '</article>';
        }

        return $html === '' ? '' : '<div class="vc-news-feed vc-news-feed-'.$layout.'" style="--vc-news-columns: '.$columns.';">'.$html.'</div>';
    }

    private function seoMeta(array $settings): string
    {
        // Page level SEO is rendered by the public layout head, so the block
        // intentionally renders nothing on the frontend.
        return '';
    }

    private function mediaUrl(mixed $value): string
    {
        if (is_array($value)) {
            $value = $value['url'] ?? $value['id'] ?? null;
        }

        if (! filled($value)) {
            return '';
        }

        return is_numeric($value) ? '/api/media/'.(int) $value : (string) $value;
    }

    private function style(array $values): string
    {
        $style = $this->inlineStyle($values);

        return $style === '' ? '' : ' style="'.e($style).'"';
    }

    private function inlineStyle(array $values): string
    {
        $style = [];

        foreach ($values as $key => $value) {
            if (! filled($value)) {
                continue;
            }

            $style[] = "{$key}: {$value}";
        }

        return implode('; ', $style);
    }

    private function size(mixed $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        return is_numeric($value) ? "{$value}px" : (string) $value;
    }

    private function gap(mixed $value): string
    {
        return match ($value) {
            'none' => '0',
            'sm' => '0.5rem',
            'lg' => '2rem',
            default => '1rem',
        };
    }

    private function maxWidth(mixed $value): string
    {
        return match ($value) {
            'sm' => '640px',
            'md' => '768px',
            'lg' => '1024px',
            'xl' => '1280px',
            '2xl' => '1536px',
            '3xl' => '1792px',
            '4xl' => '2048px',
            '5xl' => '2560px',
            '6xl' => '2880px',
            '7xl' => '3200px',
            default => filled($value) ? (string) $value : '1200px',
        };
    }
}
