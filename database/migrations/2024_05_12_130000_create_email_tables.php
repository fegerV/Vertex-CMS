<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique()->comment('Unique template key: welcome, password_reset, etc.');
            $table->string('name')->comment('Human readable name');
            $table->string('subject');
            $table->text('body_html')->comment('HTML body with Blade syntax');
            $table->text('body_text')->nullable()->comment('Plain text fallback');
            $table->json('default_vars')->nullable()->comment('Default variables with types');
            $table->string('category', 50)->default('general');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false)->comment('System templates cannot be deleted');
            $table->timestamps();
        });

        Schema::create('email_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('template_key')->index();
            $table->string('recipient_email', 500);
            $table->string('recipient_name', 255)->nullable();
            $table->string('subject', 500);
            $table->text('body_text')->nullable();
            $table->json('headers')->nullable();
            $table->json('attachments')->nullable();
            $table->json('template_vars')->nullable()->comment('Variables used for rendering');
            $table->enum('status', ['pending','sent','failed','bounced'])->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->string('message_id')->nullable()->unique()->comment('External message ID');
            $table->integer('retry_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('email_queue', function (Blueprint $table): void {
            $table->id();
            $table->string('template_key');
            $table->json('recipients')->comment('Array of email/name pairs');
            $table->json('variables')->nullable();
            $table->text('subject_override')->nullable();
            $table->text('body_override')->nullable();
            $table->integer('priority')->default(0)->index();
            $table->timestamp('scheduled_for')->nullable()->index();
            $table->integer('retry_count')->default(0);
            $table->text('last_error')->nullable();
            $table->enum('status', ['pending','processing','sent','failed','cancelled'])->default('pending')->index();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_queue');
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('email_templates');
    }
};
