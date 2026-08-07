<?php

use App\Providers\AppServiceProvider;
use App\Providers\VertexServiceProvider;
use Illuminate\Auth\AuthServiceProvider;
use Illuminate\Bus\BusServiceProvider;
use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Cookie\CookieServiceProvider;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Encryption\EncryptionServiceProvider;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Foundation\Providers\ConsoleSupportServiceProvider;
use Illuminate\Foundation\Providers\FoundationServiceProvider;
use Illuminate\Hashing\HashServiceProvider;
use Illuminate\Mail\MailServiceProvider;
use Illuminate\Queue\QueueServiceProvider;
use Illuminate\Session\SessionServiceProvider;
use Illuminate\Translation\TranslationServiceProvider;
use Illuminate\Validation\ValidationServiceProvider;
use Illuminate\View\ViewServiceProvider;
use Vertex\Forms\VertexFormsServiceProvider;

return [
    AppServiceProvider::class,
    BusServiceProvider::class,
    CacheServiceProvider::class,
    CookieServiceProvider::class,
    EncryptionServiceProvider::class,
    FilesystemServiceProvider::class,
    AuthServiceProvider::class,
    DatabaseServiceProvider::class,
    ConsoleSupportServiceProvider::class,
    FoundationServiceProvider::class,
    HashServiceProvider::class,
    MailServiceProvider::class,
    QueueServiceProvider::class,
    SessionServiceProvider::class,
    TranslationServiceProvider::class,
    ValidationServiceProvider::class,
    ViewServiceProvider::class,
    VertexServiceProvider::class,
    VertexFormsServiceProvider::class,
];
