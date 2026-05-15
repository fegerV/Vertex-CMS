<?php

namespace App\Seo\Services;

use App\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SeoContentAnalysisService
{
    /**
     * @param  Collection<int, Page>  $pages
     * @return array<string, mixed>
     */
    public function overview(Collection $pages): array
    {
        $analyses = $pages
            ->mapWithKeys(fn (Page $page): array => [$page->getKey() => $this->analyzePage($page)]);

        return [
            'totals' => [
                'pages_with_single_h1' => $analyses->filter(fn (array $analysis): bool => $analysis['h1_count'] === 1)->count(),
                'pages_missing_h1' => $analyses->filter(fn (array $analysis): bool => $analysis['h1_count'] === 0)->count(),
                'pages_with_multiple_h1' => $analyses->filter(fn (array $analysis): bool => $analysis['h1_count'] > 1)->count(),
                'images_total' => $analyses->sum(fn (array $analysis): int => $analysis['image_count']),
                'images_missing_alt' => $analyses->sum(fn (array $analysis): int => $analysis['images_missing_alt']),
            ],
            'pages' => $analyses
                ->filter(fn (array $analysis): bool => $analysis['has_issues'])
                ->sortBy([
                    fn (array $analysis): int => count($analysis['issues']) * -1,
                    fn (array $analysis): string => $analysis['title'],
                ])
                ->take(20)
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function analyzePage(Page $page): array
    {
        $sections = is_array($page->content_json['sections'] ?? null) ? $page->content_json['sections'] : [];
        $headingOutline = [];
        $textFragments = [];
        $h1Count = 0;
        $imageCount = 0;
        $imagesMissingAlt = 0;

        foreach ($sections as $section) {
            $blocks = is_array($section['blocks'] ?? null) ? $section['blocks'] : [];
            $this->walkBlocks($blocks, $headingOutline, $textFragments, $h1Count, $imageCount, $imagesMissingAlt);
        }

        $issues = [];
        $suggestions = [];
        $seoTitle = trim((string) ($page->seoMeta?->title ?? ''));
        $seoDescription = trim((string) ($page->seoMeta?->description ?? ''));
        $suggestedDescription = $this->suggestDescription($textFragments, $page->title);

        if ($h1Count === 0) {
            $issues[] = 'Нет H1 в builder-контенте';
            $suggestions[] = 'Добавьте хотя бы один блок heading с уровнем H1.';
        } elseif ($h1Count > 1) {
            $issues[] = 'На странице несколько H1';
            $suggestions[] = 'Оставьте один основной H1, а остальные заголовки понизьте до H2 или H3.';
        }

        if ($imageCount > 0 && $imagesMissingAlt > 0) {
            $issues[] = "Изображения без alt: {$imagesMissingAlt} из {$imageCount}";
            $suggestions[] = 'Заполните alt у image- и gallery-блоков для SEO и доступности.';
        }

        if ($seoDescription === '') {
            $issues[] = 'Нет отдельного meta description';
            if ($suggestedDescription !== null) {
                $suggestions[] = 'Подсказка для meta description: '.$suggestedDescription;
            }
        } elseif (mb_strlen($seoDescription) < 70) {
            $issues[] = 'Meta description выглядит слишком коротким';
            $suggestions[] = 'Добавьте больше пользы и конкретики в description, чтобы сниппет был заметнее в выдаче.';
        }

        if ($seoTitle === '' && $page->title !== '') {
            $suggestions[] = 'Если нужен более управляемый сниппет, задайте отдельный SEO title вместо fallback из обычного заголовка страницы.';
        }

        return [
            'title' => $page->title,
            'uri' => $page->uri,
            'edit_url' => route('admin.pages.edit', $page),
            'h1_count' => $h1Count,
            'heading_outline' => array_values(array_unique(array_filter($headingOutline))),
            'image_count' => $imageCount,
            'images_missing_alt' => $imagesMissingAlt,
            'suggested_description' => $suggestedDescription,
            'issues' => $issues,
            'suggestions' => $suggestions,
            'has_issues' => $issues !== [],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<int, string>  $headingOutline
     * @param  array<int, string>  $textFragments
     */
    private function walkBlocks(
        array $blocks,
        array &$headingOutline,
        array &$textFragments,
        int &$h1Count,
        int &$imageCount,
        int &$imagesMissingAlt
    ): void {
        foreach ($blocks as $block) {
            $type = (string) ($block['type'] ?? '');
            $settings = is_array($block['settings'] ?? null) ? $block['settings'] : [];

            if ($type === 'heading') {
                if (($settings['level'] ?? 'h2') === 'h1') {
                    $h1Count++;
                }

                if (filled($settings['text'] ?? null)) {
                    $headingOutline[] = trim((string) $settings['text']);
                }
            }

            if ($type === 'image') {
                $imageCount++;
                if (blank($settings['alt'] ?? null)) {
                    $imagesMissingAlt++;
                }
            }

            if ($type === 'gallery') {
                foreach (($settings['images'] ?? []) as $image) {
                    $imageCount++;
                    if (blank($image['alt'] ?? null)) {
                        $imagesMissingAlt++;
                    }
                }
            }

            $this->collectTextFragments($settings, $textFragments);

            if ($type === 'columns') {
                foreach (($settings['columns'] ?? []) as $column) {
                    $this->walkBlocks(
                        is_array($column['blocks'] ?? null) ? $column['blocks'] : [],
                        $headingOutline,
                        $textFragments,
                        $h1Count,
                        $imageCount,
                        $imagesMissingAlt
                    );
                }
            }

            if ($type === 'container') {
                $this->walkBlocks(
                    is_array($settings['blocks'] ?? null) ? $settings['blocks'] : [],
                    $headingOutline,
                    $textFragments,
                    $h1Count,
                    $imageCount,
                    $imagesMissingAlt
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $settings
     * @param  array<int, string>  $textFragments
     */
    private function collectTextFragments(array $settings, array &$textFragments): void
    {
        foreach ($settings as $key => $value) {
            if (is_string($value) && in_array($key, ['text', 'title', 'subtitle', 'content', 'question', 'answer', 'description'], true)) {
                $text = trim(strip_tags($value));
                if ($text !== '') {
                    $textFragments[] = $text;
                }
            }

            if (is_array($value)) {
                $this->collectTextFragments($value, $textFragments);
            }
        }
    }

    /**
     * @param  array<int, string>  $textFragments
     */
    private function suggestDescription(array $textFragments, string $fallbackTitle): ?string
    {
        $source = trim(implode(' ', $textFragments));

        if ($source === '') {
            $source = trim($fallbackTitle);
        }

        if ($source === '') {
            return null;
        }

        $normalized = preg_replace('/\s+/u', ' ', $source) ?? $source;

        return Str::limit($normalized, 155, '');
    }
}
