<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('seo_meta', function (Blueprint $table): void {
            $table->decimal('sitemap_priority', 2, 1)->default(0.5)->after('include_in_sitemap');
            $table->string('sitemap_changefreq', 20)->default('weekly')->after('sitemap_priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seo_meta', function (Blueprint $table): void {
            $table->dropColumn(['sitemap_priority', 'sitemap_changefreq']);
        });
    }
};
