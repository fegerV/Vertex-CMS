<?php

namespace App\AI\Services;

use App\Builder\Services\PageBuilderService;
use InvalidArgumentException;

class VisualPageGenerator
{
    public function __construct(private readonly PageBuilderService $builder) {}

    public function generate(array $brief, callable $generator): array
    {
        $prompt = ['title' => trim((string) ($brief['title'] ?? '')), 'goal' => trim((string) ($brief['goal'] ?? '')), 'audience' => trim((string) ($brief['audience'] ?? '')), 'style' => $brief['style'] ?? 'modern'];
        if ($prompt['title'] === '' || $prompt['goal'] === '') {
            throw new InvalidArgumentException('Page title and goal are required.');
        }
        $document = $generator($prompt);
        if (! is_array($document)) {
            throw new InvalidArgumentException('AI generator must return builder JSON.');
        }
        $sections = $this->builder->normalizeSections($document['sections'] ?? $document);
        $errors = $this->builder->validateBlocks($sections);
        if ($errors !== []) {
            throw new InvalidArgumentException(implode(' ', $errors));
        }

        return ['version' => '1.0', 'layout' => $document['layout'] ?? 'default', 'sections' => $sections];
    }
}
