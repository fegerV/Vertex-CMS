<?php

namespace App\Contracts;

interface CacheInvalidatorContract
{
    /** Invalidate a named cache domain such as settings, pages, menus, or all. */
    public function invalidate(string $domain, array $context = []): void;
}
