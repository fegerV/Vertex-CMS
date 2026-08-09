<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_submissions', function (Blueprint $table): void {
            $table->string('resume_token_hash', 64)->nullable()->unique()->after('idempotency_key');
            $table->timestamp('resume_expires_at')->nullable()->after('resume_token_hash');
        });
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table): void {
            $table->dropUnique(['resume_token_hash']);
            $table->dropColumn(['resume_token_hash', 'resume_expires_at']);
        });
    }
};
