<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('vertex:about', function (): void {
    $this->comment('VertexCMS MVP v0.1 skeleton is installed.');
});

