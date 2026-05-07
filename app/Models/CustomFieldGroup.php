<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldGroup extends Model
{
    protected $fillable = [
        'name',
        'handle',
        'scope',
        'description',
        'fields_json',
        'rules_json',
        'is_system',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fields_json' => 'array',
        'rules_json' => 'array',
        'is_system' => 'boolean',
    ];

    public function appliesToPageTemplate(?string $template): bool
    {
        $scope = (string) ($this->scope ?: 'all_pages');
        $rules = is_array($this->rules_json) ? $this->rules_json : [];
        $template = trim((string) ($template ?: 'default'));

        return match ($scope) {
            'all_pages' => true,
            'template' => in_array($template, array_map('strval', $rules['templates'] ?? []), true),
            'except_template' => !in_array($template, array_map('strval', $rules['templates'] ?? []), true),
            default => true,
        };
    }
}
