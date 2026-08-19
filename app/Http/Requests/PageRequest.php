<?php

namespace App\Http\Requests;

use App\Content\Services\PageService;
use App\Seo\Services\SeoMetaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'integer', Rule::exists('pages', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(PageService::STATUSES)],
            'template' => ['nullable', 'string', 'max:255'],
            'content_json' => ['nullable', 'string'],
            'custom_fields_json' => ['nullable', 'string'],
            'term_ids' => ['nullable', 'array'],
            'term_ids.*' => ['integer', Rule::exists('terms', 'id')],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_canonical_url' => ['nullable', 'url', 'max:500'],
            'seo_robots' => ['nullable', Rule::in(SeoMetaService::ROBOTS)],
            'seo_og_title' => ['nullable', 'string', 'max:255'],
            'seo_og_description' => ['nullable', 'string', 'max:500'],
            'seo_og_image' => ['nullable', 'integer'],
            'seo_schema_json' => ['nullable', 'json'],
            'seo_include_in_sitemap' => ['nullable', 'boolean'],
        ];
    }

    public function sanitized(): array
    {
        return [
            ...$this->validated(),
            'seo_include_in_sitemap' => $this->boolean('seo_include_in_sitemap'),
        ];
    }
}