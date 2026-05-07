<?php

namespace App\Builder\Config;

class BlockRegistry
{
    protected static array $blocks = [];
    protected static array $categories = [];

    public static function register(string $type, array $config): void
    {
        self::$blocks[$type] = $config;
        
        if (isset($config['category'])) {
            self::$categories[$config['category']][$type] = $config;
        }
    }

    public static function get(string $type): ?array
    {
        return self::$blocks[$type] ?? null;
    }

    public static function all(): array
    {
        return self::$blocks;
    }

    public static function byCategory(): array
    {
        return self::$categories;
    }

    public static function getCategories(): array
    {
        return array_keys(self::$categories);
    }
}