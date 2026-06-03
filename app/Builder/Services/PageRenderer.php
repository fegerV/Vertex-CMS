<?php

namespace App\Builder\Services;

use Vertex\Forms\Services\FormService;
use Vertex\Forms\Models\Form;
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
                 'divider' => '<hr class="vc-divider">',
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
        $level = in_array($settings['level'] ?? 'h2', ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true)
            ? $settings['level']
            : 'h2';

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
        $url = $settings['url'] ?? '';

        if ($url === '') {
            return '<div class="vc-media-placeholder vc-video-placeholder">Video placeholder</div>';
        }

        return '<div class="vc-video"><iframe src="'.e($url).'" loading="lazy" allowfullscreen></iframe></div>';
    }

    private function html(array $settings): string
    {
        $html = strip_tags((string) ($settings['html'] ?? ''), '<p><br><strong><b><em><i><ul><ol><li><a><span><div>');
        $html = preg_replace('/\son\w+=(["\']).*?\1/i', '', $html) ?? $html;

        return str_ireplace(['javascript:', 'data:'], '', $html);
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
