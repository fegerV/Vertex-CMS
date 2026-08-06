<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gdpr_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->string('banner_title')->default('Мы используем файлы cookie');
            $table->text('banner_message')->default('Этот сайт использует файлы cookie для улучшения пользовательского опыта. Продолжая использовать сайт, вы соглашаетесь с нашей политикой использования файлов cookie.');
            $table->string('accept_button_text')->default('Принять');
            $table->string('decline_button_text')->default('Отклонить');
            $table->string('policy_link')->nullable();
            $table->integer('cookie_duration_days')->default(365);
            $table->timestamps();
        });

        Schema::create('ip_filters', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 100);
            $table->enum('type', ['blacklist', 'whitelist']);
            $table->string('reason')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['ip_address', 'type']);
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_filters');
        Schema::dropIfExists('gdpr_settings');
    }
};
