<?php

namespace Vertex\Forms\Services;

use Vertex\Forms\Models\Form;
use Vertex\Forms\Models\FormField;

class FormImportExportService
{
    /**
     * Export form definition as JSON.
     */
    public function export(Form $form): array
    {
        $form->load("fields");

        return [
            "form" => [
                "id" => $form->id,
                "name" => $form->name,
                "slug" => $form->slug,
                "type" => $form->type,
                "description" => $form->description,
                "settings" => $form->settingsWithoutSecrets(),
                "fields" => $form->fields->map(fn ($f) => [
                    "id" => $f->id,
                    "name" => $f->name,
                    "label" => $f->label,
                    "type" => $f->type,
                    "sort_order" => $f->sort_order,
                    "required" => $f->required,
                    "visible" => $f->visible,
                    "options" => $f->options,
                    "default_value" => $f->default_value,
                    "placeholder" => $f->placeholder,
                    "help_text" => $f->help_text,
                    "css_class" => $f->css_class,
                ])->values(),
            ],
        ];
    }

    /**
     * Import form from JSON array.
     * Creates a new form (or duplicates existing).
     */
    public function import(array $data, ?int $existingFormId = null): Form
    {
        $formData = $data["form"] ?? $data;

        // Ensure unique slug
        $originalSlug = $formData["slug"];
        $slug = $originalSlug;
        $i = 1;
        while (Form::where("slug", $slug)->exists()) {
            $slug = $originalSlug . "-import-" . $i++;
        }

        $form = Form::create([
            "name" => $formData["name"],
            "slug" => $slug,
            "type" => $formData["type"] ?? "standard",
            "description" => $formData["description"] ?? null,
            "settings" => $formData["settings"] ?? [],
            "is_active" => true,
        ]);

        // Create fields
        foreach ($formData["fields"] ?? [] as $fieldData) {
            FormField::create([
                "form_id" => $form->id,
                "name" => $fieldData["name"],
                "label" => $fieldData["label"],
                "type" => $fieldData["type"],
                "sort_order" => $fieldData["sort_order"] ?? 0,
                "required" => $fieldData["required"] ?? false,
                "visible" => $fieldData["visible"] ?? true,
                "options" => $fieldData["options"] ?? [],
                "default_value" => $fieldData["default_value"] ?? null,
                "placeholder" => $fieldData["placeholder"] ?? null,
                "help_text" => $fieldData["help_text"] ?? null,
                "css_class" => $fieldData["css_class"] ?? null,
            ]);
        }

        return $form;
    }
}
