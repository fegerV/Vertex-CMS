<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use App\Models\Term;
use Illuminate\Database\Seeder;

class TaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $category = Taxonomy::query()->firstOrCreate(
            ['slug' => 'category'],
            [
                'name' => 'Category',
                'entity_type' => 'page',
                'hierarchical' => true,
                'settings_json' => [],
            ]
        );

        $tag = Taxonomy::query()->firstOrCreate(
            ['slug' => 'tag'],
            [
                'name' => 'Tag',
                'entity_type' => 'page',
                'hierarchical' => false,
                'settings_json' => [],
            ]
        );

        foreach ([
            [$category->id, 'General', 'general'],
            [$category->id, 'Services', 'services'],
            [$tag->id, 'Featured', 'featured'],
            [$tag->id, 'Mobile', 'mobile'],
        ] as [$taxonomyId, $name, $slug]) {
            Term::query()->firstOrCreate(
                ['taxonomy_id' => $taxonomyId, 'slug' => $slug],
                [
                    'name' => $name,
                    'description' => null,
                    'sort_order' => 0,
                    'seo_json' => [],
                ]
            );
        }
    }
}
