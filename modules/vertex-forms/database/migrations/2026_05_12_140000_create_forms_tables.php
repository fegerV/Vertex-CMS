<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('standard'); // standard, calculator, survey, poll
            $table->text('description')->nullable();
            $table->json('settings')->nullable(); // general settings: theme, css, notifications, anti-spam, limits
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('require_login')->default(false);
            $table->integer('entry_limit')->nullable()->comment('Max total submissions');
            $table->integer('daily_limit')->nullable()->comment('Max per day per IP/user');
            $table->dateTime('available_from')->nullable();
            $table->dateTime('available_to')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('form_fields', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->string('name')->comment('Field name/ID');
            $table->string('label');
            $table->string('type'); // text, number, email, tel, textarea, select, radio, checkbox, checkbox_group, date, file, hidden, calculator, heading, divider, html, page_break
            $table->integer('sort_order')->default(0);
            $table->json('options')->nullable(); // select options, validation rules, conditional logic, calculator formula
            $table->boolean('required')->default(false);
            $table->boolean('visible')->default(true);
            $table->string('default_value')->nullable();
            $table->text('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->string('css_class')->nullable();
            $table->timestamps();
        });

        Schema::create('form_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->string('submission_id')->unique()->comment('Public UUID for reference');
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('user_agent')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('completed'); // completed, pending, spam, trashed
            $table->json('meta')->nullable(); // total calculated, payment_status, etc.
            $table->timestamps();
        });

        Schema::create('form_submission_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('submission_id')->constrained('form_submissions')->cascadeOnDelete();
            $table->foreignId('field_id')->constrained('form_fields')->cascadeOnDelete();
            $table->text('value')->nullable(); // stored as JSON for arrays (checkboxes, files)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submission_values');
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('forms');
    }
};
