<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_revisions', function (Blueprint $table) {
            $table->index('page_id');
            $table->index('user_id');
            $table->index('created_at');
            $table->index(['page_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('page_revisions', function (Blueprint $table) {
            $table->dropIndex(['page_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['page_id', 'created_at']);
        });
    }
};
