<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Maintenance settings will be added via SettingCatalog and seeder
        // No schema changes needed - uses existing settings table
    }

    public function down(): void
    {
        // Nothing to rollback
    }
};
