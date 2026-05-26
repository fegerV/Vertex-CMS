@extends('admin.layouts.app')

@section('title', 'Form Builder')
@section('page_title', $form?->name ? 'Edit: ' . $form->name : 'New Form')
@section('page_wrap_class', 'vc-page-wrap-forms-builder')
@section('content')
@php
    $builderPayload = [
        'id' => $form?->id,
        'name' => $form?->name,
        'slug' => $form?->slug,
        'type' => $form?->type ?? 'standard',
        'description' => $form?->description,
        'is_active' => $form?->is_active ?? false,
        'settings' => $form?->settings ?? [],
        'fields' => $form?->fields?->map(fn ($field) => [
            'id' => $field->id,
            'name' => $field->name,
            'label' => $field->label,
            'type' => $field->type,
            'sort_order' => $field->sort_order,
            'required' => $field->required,
            'visible' => $field->visible,
            'options' => $field->options,
            'default_value' => $field->default_value,
            'placeholder' => $field->placeholder,
            'help_text' => $field->help_text,
            'css_class' => $field->css_class,
        ])->values()->all() ?? [],
    ];
@endphp

<div class="vc-forms-builder-host">
    <div
        data-vc-form-builder
        data-registry-url="{{ url('/admin/api/forms/field-registry') }}"
        data-store-url="{{ url('/admin/api/forms') }}"
        data-update-url-template="{{ url('/admin/api/forms/__FORM_ID__') }}"
        data-submissions-url-template="{{ url('/admin/forms/__FORM_ID__/submissions') }}"
        data-analytics-url-template="{{ url('/admin/forms/__FORM_ID__/analytics') }}"
        data-builder-route-template="{{ route('admin.forms.builder', ['form' => '__FORM_ID__']) }}"
        data-public-preview-url="{{ $form?->slug ? url('/forms/' . $form->slug) : '' }}"
        data-exit-url="{{ route('admin.forms.index') }}"
        data-initial-form='@json($builderPayload)'
        class="min-h-[calc(100vh-9rem)]"
    ></div>
</div>
@endsection
