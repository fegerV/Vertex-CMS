<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redirect_logs', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('referer')->nullable();
            $table->integer('status_code')->default(404);
            $table->string('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            $table->index(['status_code', 'created_at']);
            $table->index('url');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirect_logs');
    }
};
