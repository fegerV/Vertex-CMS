<?php

namespace App\AI\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AiDraftService
{
    public function generate(string $action, string $instruction, array $context = []): array
    {
        $pageTitle = trim((string) Arr::get($context, 'page.title', 'New page'));
        $pageUri = trim((string) Arr::get($context, 'page.uri', '/'));
        $brandVoice = trim((string) config_value('ai.brand_voice', ''));
        $language = trim((string) config_value('ai.content_language', 'en'));
        $summary = trim($instruction) !== '' ? trim($instruction) : "Create a {$action} draft for {$pageTitle}.";

        return match ($action) {
            'faq' => $this->faqDraft($pageTitle, $summary, $language, $brandVoice),
            'cta' => $this->ctaDraft($pageTitle, $pageUri, $summary),
            'seo' => $this->seoDraft($pageTitle, $pageUri, $summary, $brandVoice),
            'builder' => $this->builderDraft($pageTitle, $pageUri, $summary, $brandVoice),
            default => $this->textDraft($pageTitle, $summary, $brandVoice),
        };
    }

    private function textDraft(string $pageTitle, string $summary, string $brandVoice): array
    {
        $lines = [
            "{$pageTitle} helps visitors understand the offer quickly and move toward a clear next step.",
            "Use this section to explain the main value, remove hesitation, and reinforce why the page matters right now.",
        ];

        if ($brandVoice !== '') {
            $lines[] = "Tone reference: {$brandVoice}.";
        }

        $lines[] = "Working brief: {$summary}";

        $text = implode("\n\n", $lines);

        return [
            'kind' => 'blocks',
            'preview' => $text,
            'apply_label' => 'Add text block',
            'blocks' => [
                $this->block('text', [
                    'content' => $text,
                    'align' => 'left',
                ]),
            ],
        ];
    }

    private function faqDraft(string $pageTitle, string $summary, string $language, string $brandVoice): array
    {
        $items = [
            [
                'question' => "What does {$pageTitle} include?",
                'answer' => "It outlines the core scope, expected outcome, and the fastest way to start. {$summary}",
            ],
            [
                'question' => "Who is this page for?",
                'answer' => "It is intended for visitors who need a clear explanation, practical next steps, and confidence before contacting the team.",
            ],
            [
                'question' => "How should the tone sound?",
                'answer' => $brandVoice !== ''
                    ? "Use {$brandVoice} while staying concise and easy to scan."
                    : "Use a concise, direct tone that is easy to scan.",
            ],
        ];

        return [
            'kind' => 'blocks',
            'preview' => collect($items)->map(fn (array $item) => "{$item['question']}\n{$item['answer']}")->implode("\n\n"),
            'apply_label' => 'Add FAQ block',
            'blocks' => [
                $this->block('faq', [
                    'items' => $items,
                    'language' => $language,
                ]),
            ],
        ];
    }

    private function ctaDraft(string $pageTitle, string $pageUri, string $summary): array
    {
        $text = Str::limit($summary, 72, '');

        return [
            'kind' => 'blocks',
            'preview' => "CTA: Start with {$pageTitle}\nSupport: {$text}",
            'apply_label' => 'Add CTA block',
            'blocks' => [
                $this->block('heading', [
                    'text' => "Ready to move forward with {$pageTitle}?",
                    'level' => 'h2',
                ]),
                $this->block('text', [
                    'content' => $text !== '' ? $text : 'Use this call to action to direct visitors to the next conversion step.',
                ]),
                $this->block('button', [
                    'text' => 'Request a consultation',
                    'url' => $pageUri !== '' ? $pageUri : '#',
                    'style' => 'primary',
                    'target' => '_self',
                ]),
            ],
        ];
    }

    private function seoDraft(string $pageTitle, string $pageUri, string $summary, string $brandVoice): array
    {
        $seoTitle = Str::limit("{$pageTitle} | Practical guide and next steps", 60, '');
        $description = Str::limit(
            trim("{$pageTitle} explained clearly with practical value, next steps, and conversion-ready messaging. {$summary} {$brandVoice}"),
            160,
            ''
        );

        return [
            'kind' => 'seo',
            'preview' => "{$seoTitle}\n\n{$description}",
            'apply_label' => 'Apply SEO draft',
            'seo' => [
                'title' => $seoTitle,
                'description' => $description,
            ],
        ];
    }

    private function builderDraft(string $pageTitle, string $pageUri, string $summary, string $brandVoice): array
    {
        $content = [
            'version' => '1.0',
            'layout' => 'default',
            'settings' => [
                'container' => '1200px',
                'background' => '#ffffff',
            ],
            'sections' => [
                [
                    'id' => (string) Str::uuid(),
                    'blocks' => [
                        $this->block('heading', [
                            'text' => $pageTitle,
                            'level' => 'h1',
                        ]),
                        $this->block('text', [
                            'content' => "Use this draft as a structured starting point for {$pageTitle}. {$summary}",
                        ]),
                        $this->block('button', [
                            'text' => 'Get started',
                            'url' => $pageUri !== '' ? $pageUri : '#',
                            'style' => 'primary',
                            'target' => '_self',
                        ]),
                        $this->block('faq', [
                            'items' => [
                                [
                                    'question' => "Why choose {$pageTitle}?",
                                    'answer' => $brandVoice !== ''
                                        ? "Because it can be presented with a {$brandVoice} tone while keeping the page conversion-focused."
                                        : 'Because it combines clarity, structure, and a clear next action.',
                                ],
                            ],
                        ]),
                    ],
                ],
            ],
        ];

        return [
            'kind' => 'builder',
            'preview' => json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'apply_label' => 'Replace page JSON',
            'builder' => $content,
        ];
    }

    private function block(string $type, array $settings): array
    {
        return [
            'id' => (string) Str::uuid(),
            'type' => $type,
            'settings' => $settings,
        ];
    }
}
