<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoKeywordMap extends Model
{
    use HasFactory;

    protected $table = 'seo_keyword_maps';

    protected $fillable = [
        'name',
        'target_url',
        'keywords',
        'is_enabled',
        'case_sensitive',
        'max_links_per_post',
        'auto_link_on_publish',
        'description',
        'ai_variants',
    ];

    protected $casts = [
        'keywords' => 'array',
        'ai_variants' => 'array',
        'is_enabled' => 'boolean',
        'case_sensitive' => 'boolean',
        'auto_link_on_publish' => 'boolean',
        'max_links_per_post' => 'integer',
    ];

    /**
     * Получить все ключевые фразы включая AI варианты
     */
    public function getAllKeywords(): array
    {
        $keywords = $this->keywords ?? [];
        $aiVariants = $this->ai_variants ?? [];
        
        return array_unique(array_merge($keywords, $aiVariants));
    }

    /**
     * Добавить AI-варианты к ключевым словам
     */
    public function addAiVariants(array $variants): void
    {
        $currentVariants = $this->ai_variants ?? [];
        $this->ai_variants = array_unique(array_merge($currentVariants, $variants));
        $this->save();
    }

    /**
     * Проверить соответствие ключевой фразы
     */
    public function matchesKeyword(string $keyword): bool
    {
        $allKeywords = $this->getAllKeywords();
        
        if ($this->case_sensitive) {
            return in_array($keyword, $allKeywords, true);
        }
        
        foreach ($allKeywords as $mapKeyword) {
            if (strcasecmp($keyword, $mapKeyword) === 0) {
                return true;
            }
            // Проверка вхождения
            if (stripos($keyword, $mapKeyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
}
