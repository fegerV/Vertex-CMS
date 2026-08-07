<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_submissions', function (Blueprint $table): void {
            $table->string('idempotency_key', 64)->nullable()->after('submission_id');
            $table->unique(['form_id', 'idempotency_key'], 'form_submissions_form_id_idempotency_unique');
        });
    }

    public function down(): void
    {
        Schema::table('form_submissions', function (Blueprint $table): void {
            $table->dropUnique('form_submissions_form_id_idempotency_unique');
            $table->dropColumn('idempotency_key');
        });
    }
};
