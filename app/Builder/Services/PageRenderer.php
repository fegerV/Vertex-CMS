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

    public function render(?array $content): HtmlString
    {
        $sections = $content['sections'] ?? [];

        if (! is_array($sections) || $sections === []) {
            return new HtmlString('');
        }

        return new HtmlString(collect($sections)
            ->map(fn (array $section) => $this->renderSection($section))
            ->implode(''));
    }

    private function renderSection(array $section): string
    {
        $blocks = $section['blocks'] ?? $section['children'] ?? [];
        $sectionSettings = is_array($section['settings'] ?? null) ? $section['settings'] : [];
        $style = $this->style([
            'background-color' => $sectionSettings['background_color'] ?? $section['background_color'] ?? null,
            'padding-top' => $this->size($sectionSettings['padding_top'] ?? $section['padding_top'] ?? null),
            'padding-bottom' => $this->size($sectionSettings['padding_bottom'] ?? $section['padding_bottom'] ?? null),
        ]);
        $class = trim('vc-section '.($sectionSettings['css_class'] ?? $section['css_class'] ?? ''));

        return '<section class="'.e($class).'"'.$style.'><div class="vc-container">'
            .collect($blocks)->map(fn (array $block) => $this->renderBlock($block))->implode('')
            .'</div></section>';
    }

    private function renderBlock(array $block): string
    {
        $type = Str::of($block['type'] ?? 'unknown')->lower()->value();
        $settings = $block['settings'] ?? $block;
        $override = $this->themes->blockView($type);

        if ($override) {
            $html = match ($type) {
                'html' => $this->html($settings),
                'form' => $this->form($settings),
                default => null,
            };

            return view($override, array_merge($settings, [
                'block' => $block,
                'settings' => $settings,
                'html' => $html,
            ]))->render();
        }

         return match ($type) {
             'heading' => $this->heading($settings),
             'text' => $this->text($settings),
             'button' => $this->button($settings),
             'divider' => '<hr class="vc-divider">',
             'faq' => $this->faq($settings),
             'html' => $this->html($settings),
             'form' => $this->form($settings),
             'hero' => $this->hero($settings),
             'gallery' => $this->gallery($settings),
             default => '<!-- Unknown VertexCMS block: '.e($type).' -->',
         };
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

        return '<div class="vc-faq">'.collect($items)->map(function (array $item): string {
            return '<details class="vc-faq-item"><summary>'.e($item['question'] ?? '').'</summary><div>'
                .nl2br(e($item['answer'] ?? '')).'</div></details>';
        })->implode('').'</div>';
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

    private function style(array $values): string
    {
        $style = $this->inlineStyle($values);

        return $style === '' ? '' : ' style="'.e($style).'"';
    }

    private function inlineStyle(array $values): string
    {
        return collect($values)
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value, string $key) => "{$key}: {$value}")
            ->implode('; ');
    }

    private function size(mixed $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        return is_numeric($value) ? "{$value}px" : (string) $value;
    }
}
