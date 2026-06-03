<?php

namespace App\Builder\Config;

class BlockPackRegistry
{
    public static function for(string $type): array
    {
        return (array) data_get(BlockRegistry::get($type) ?? [], 'editor.packs', []);
    }

    public static function all(): array
    {
        return collect(BlockRegistry::all())
            ->map(fn (array $block): array => (array) data_get($block, 'editor.packs', []))
            ->filter(fn (array $packs): bool => $packs !== [])
            ->all();
    }
}
