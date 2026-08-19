<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModulesSeeder extends Seeder
{
    public function run(): void
    {
        $modules = ['Core', 'Admin', 'Auth', 'Content', 'Builder', 'Seo', 'Media', 'Performance', 'System'];

        foreach ($modules as $module) {
            Module::query()->firstOrCreate(
                ['slug' => strtolower($module)],
                [
                    'name' => $module,
                    'version' => config('vertex.version'),
                    'type' => 'core',
                    'status' => 'enabled',
                ]
            );
        }
    }
}

