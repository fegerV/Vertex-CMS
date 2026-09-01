<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('group_name');
            $table->string('setting_key');
            $table->longText('setting_value')->nullable();
            $table->string('type', 50)->default('string');
            $table->boolean('autoload')->default(true);
            $table->timestamps();
            $table->unique(['group_name', 'setting_key'], 'settings_group_key_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

