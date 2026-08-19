<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('uri', 500)->unique();
            $table->string('status', 50)->default('draft');
            $table->string('template')->default('default');
            $table->longText('content_json')->nullable();
            $table->longText('custom_fields_json')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['parent_id', 'slug']);
        });

        Schema::create('page_revisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->longText('content_json')->nullable();
            $table->longText('custom_fields_json')->nullable();
            $table->longText('seo_json')->nullable();
            $table->string('action', 100)->default('save');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('custom_field_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('handle')->unique();
            $table->string('scope', 50)->default('all_pages');
            $table->text('description')->nullable();
            $table->longText('fields_json');
            $table->longText('rules_json')->nullable();
            $table->boolean('is_system')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('seo_meta', function (Blueprint $table): void {
            $table->id();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('canonical_url', 500)->nullable();
            $table->string('robots', 100)->default('index, follow');
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->unsignedBigInteger('og_image')->nullable();
            $table->longText('schema_json')->nullable();
            $table->boolean('include_in_sitemap')->default(true);
            $table->timestamps();
            $table->unique(['entity_type', 'entity_id'], 'seo_entity_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_meta');
        Schema::dropIfExists('custom_field_groups');
        Schema::dropIfExists('page_revisions');
        Schema::dropIfExists('pages');
    }
};
