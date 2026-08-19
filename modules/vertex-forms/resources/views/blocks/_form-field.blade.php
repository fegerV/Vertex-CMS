@php
    $fieldName = $field['name'] ?? '';
    $fieldLabel = $field['label'] ?? '';
    $fieldType = $field['type'] ?? 'text';
    $required = $field['required'] ?? false;
    $placeholder = $field['placeholder'] ?? '';
    $helpText = $field['help_text'] ?? '';
    $cssClass = $field['css_class'] ?? '';
    $validation = $field['validation'] ?? '';
    $options = $field['options'] ?? [];
    $defaultValue = $field['default_value'] ?? '';
    $conditional = $field['conditional'] ?? null;
    $calculator = $field['calculator'] ?? null;
    $width = $field['width'] ?? 'full';
    $columnWidth = $field['column_width'] ?? 12;
    $nameColumnsClass = ($options['collect_middle_name'] ?? false) ? 'md:grid-cols-3' : 'md:grid-cols-2';
    $acceptedMimeTypes = isset($options['mime_types'])
        ? (is_array($options['mime_types']) ? implode(',', $options['mime_types']) : $options['mime_types'])
        : '';
@endphp

<div 
    class="vc-form-field mb-4"
>
    @if($fieldType !== 'heading' && $fieldType !== 'divider' && $fieldType !== 'html')
        <label class="vc-field-label block text-sm font-medium text-gray-700 mb-1" for="field_{{ $fieldName }}">
            {{ $fieldLabel }}
            @if($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif

@switch($fieldType)
    @case('text')
    @case('email')
    @case('tel')
    @case('number')
    @case('date')
    @case('time')
    @case('url')
    @case('datetime-local')
    @case('password')
        <input 
            type="{{ $fieldType }}" 
            id="field_{{ $fieldName }}"
            name="{{ $fieldName }}" 
            x-model="formData['{{ $fieldName }}']"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error($fieldName) border-red-500 @enderror"
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            @if($required) required @endif
            @if(isset($options['min'])) min="{{ $options['min'] }}" @endif
            @if(isset($options['max'])) max="{{ $options['max'] }}" @endif
            @if(isset($options['step'])) step="{{ $options['step'] }}" @endif
        >
        @break

    @case('name')
        <div class="grid gap-3 {{ $nameColumnsClass }}">
            <input
                type="text"
                id="field_{{ $fieldName }}_first"
                name="{{ $fieldName }}[first]"
                x-model="formData['{{ $fieldName }}'].first"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="{{ $options['first_placeholder'] ?? 'First name' }}"
                @if($required) required @endif
            >
            @if($options['collect_middle_name'] ?? false)
                <input
                    type="text"
                    id="field_{{ $fieldName }}_middle"
                    name="{{ $fieldName }}[middle]"
                    x-model="formData['{{ $fieldName }}'].middle"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="Middle name"
                >
            @endif
            <input
                type="text"
                id="field_{{ $fieldName }}_last"
                name="{{ $fieldName }}[last]"
                x-model="formData['{{ $fieldName }}'].last"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="{{ $options['last_placeholder'] ?? 'Last name' }}"
                @if($required) required @endif
            >
        </div>
        @break

    @case('address')
        <div class="grid gap-3">
            <input
                type="text"
                id="field_{{ $fieldName }}_street"
                name="{{ $fieldName }}[street]"
                x-model="formData['{{ $fieldName }}'].street"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                placeholder="Street address"
                @if($required) required @endif
            >
            <div class="grid gap-3 md:grid-cols-2">
                <input
                    type="text"
                    name="{{ $fieldName }}[city]"
                    x-model="formData['{{ $fieldName }}'].city"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="City"
                    @if($required) required @endif
                >
                @if($options['show_region'] ?? true)
                    <input
                        type="text"
                        name="{{ $fieldName }}[region]"
                        x-model="formData['{{ $fieldName }}'].region"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="State / Region"
                    >
                @endif
            </div>
            <div class="grid gap-3 md:grid-cols-2">
                @if($options['show_postal_code'] ?? true)
                    <input
                        type="text"
                        name="{{ $fieldName }}[postal_code]"
                        x-model="formData['{{ $fieldName }}'].postal_code"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Postal code"
                    >
                @endif
                @if($options['show_country'] ?? true)
                    <input
                        type="text"
                        name="{{ $fieldName }}[country]"
                        x-model="formData['{{ $fieldName }}'].country"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Country"
                    >
                @endif
            </div>
        </div>
        @break

    @case('textarea')
        <textarea 
            id="field_{{ $fieldName }}"
            name="{{ $fieldName }}" 
            x-model="formData['{{ $fieldName }}']"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error($fieldName) border-red-500 @enderror"
            rows="{{ $options['rows'] ?? 4 }}"
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            @if($required) required @endif
        ></textarea>
        @break

    @case('select')
        <select 
            id="field_{{ $fieldName }}"
            name="{{ $fieldName }}" 
            x-model="formData['{{ $fieldName }}']"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            @if($required) required @endif
        >
            <option value="">{{ $placeholder ?: __('forms.select_placeholder') }}</option>
            @foreach($options['choices'] ?? [] as $choice)
                <option value="{{ $choice['value'] }}">{{ $choice['label'] }}</option>
            @endforeach
        </select>
        @break

    @case('radio')
        <div class="space-y-2" id="field_{{ $fieldName }}">
            @foreach($options['choices'] ?? [] as $choice)
                <label class="flex items-center">
                    <input 
                        type="radio" 
                        name="{{ $fieldName }}" 
                        :value="{{ $choice['value'] }}"
                        x-model="formData['{{ $fieldName }}']"
                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                        @if($required) required @endif
                    >
                    <span class="ml-2 text-sm text-gray-700">{{ $choice['label'] }}</span>
                </label>
            @endforeach
        </div>
        @break

    @case('checkbox')
        @case('checkbox_group')
        @if($fieldType === 'checkbox' && $fieldLabel)
            <label class="flex items-center">
                <input 
                    type="checkbox" 
                    id="field_{{ $fieldName }}"
                    name="{{ $fieldName }}" 
                    x-model="formData['{{ $fieldName }}']"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    value="1"
                    @if($required) required @endif
                >
                <span class="ml-2 text-sm text-gray-700">{{ $fieldLabel }}</span>
            </label>
        @else
            <div class="space-y-2" id="field_{{ $fieldName }}">
                @foreach($options['choices'] ?? [] as $choice)
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="{{ $fieldName }}[]" 
                            :value="{{ $choice['value'] }}"
                            x-model="formData['{{ $fieldName }}']"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <span class="ml-2 text-sm text-gray-700">{{ $choice['label'] }}</span>
                    </label>
                @endforeach
            </div>
        @endif
        @break

    @case('file')
        <input 
            type="file" 
            id="field_{{ $fieldName }}"
            name="{{ $fieldName }}" 
            x-on:change="formData['{{ $fieldName }}'] = $event.target.files"
            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
            @if($required) required @endif
            @if(isset($options['multiple']) && $options['multiple']) multiple @endif
            accept="{{ $acceptedMimeTypes }}"
        >
        @if(isset($options['max_size']))
            <p class="text-xs text-gray-500 mt-1">{{ __('forms.validation_file_too_big', ['max' => $options['max_size']]) }}</p>
        @endif
        @break

    @case('consent')
        <label class="flex items-start gap-2">
            <input
                type="checkbox"
                id="field_{{ $fieldName }}"
                name="{{ $fieldName }}"
                x-model="formData['{{ $fieldName }}']"
                class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                value="1"
                @if($required) required @endif
            >
            <span class="text-sm text-gray-700">{!! $options['consent_text'] ?? $fieldLabel !!}</span>
        </label>
        @break

    @case('rating')
        <div class="flex flex-wrap gap-2" id="field_{{ $fieldName }}">
            @for($rating = 1; $rating <= (int) ($options['scale'] ?? 5); $rating++)
                <label class="cursor-pointer">
                    <input
                        type="radio"
                        name="{{ $fieldName }}"
                        value="{{ $rating }}"
                        x-model="formData['{{ $fieldName }}']"
                        class="sr-only"
                        @if($required) required @endif
                    >
                    <span
                        class="inline-flex h-10 min-w-10 items-center justify-center rounded-full border border-gray-300 bg-white px-3 text-sm font-semibold text-gray-700"
                        :class="Number(formData['{{ $fieldName }}']) >= {{ $rating }} ? 'border-blue-500 bg-blue-50 text-blue-700' : ''"
                    >
                        {{ ($options['style'] ?? 'stars') === 'stars' ? '★' : $rating }}
                    </span>
                </label>
            @endfor
        </div>
        @break

    @case('calculator')
        <div class="vc-calculator-field p-4 bg-gray-50 rounded-md">
            <div class="mb-2 text-sm text-gray-600">{{ $helpText ?: 'Калькулятор' }}</div>
            <div class="flex items-center gap-2">
                @foreach($options['depends_on'] ?? [] as $dep)
                    <input 
                        type="number" 
                        name="{{ $dep }}" 
                        x-model="formData['{{ $dep }}']"
                        @input="calculateField(fields.find(f => f.name === '{{ $fieldName }}'))"
                        class="w-24 px-3 py-2 border rounded-md"
                        placeholder="{{ $dep }}"
                    >
                @endforeach
                <span class="text-gray-500">=</span>
                <input 
                    type="text" 
                    id="field_{{ $fieldName }}"
                    name="{{ $fieldName }}" 
                    x-model="formData['{{ $fieldName }}']"
                    readonly
                    class="flex-1 px-4 py-2 bg-white border border-blue-300 rounded-md text-blue-600 font-bold text-center"
                >
                @if(isset($options['prefix']))
                    <span class="text-sm text-gray-500">{{ $options['prefix'] }}</span>
                @endif
            </div>
                <div x-text="`{{ __('forms.result') }} ${formData['{{ $fieldName }}'] || '0'}`" class="mt-2 text-lg font-semibold text-blue-600"></div>
        </div>
        @break

    @case('heading')
        <h4 class="text-lg font-semibold mt-6 mb-2">{{ $fieldLabel }}</h4>
        @break

    @case('divider')
        <hr class="my-4 border-gray-200">
        @break

    @case('html')
        <div class="prose max-w-none">
            {!! $options['content'] ?? '' !!}
        </div>
        @break

    @case('page_break')
        {{-- Hidden, used for multi-page --}}
        @break

    @default
        <input 
            type="text" 
            name="{{ $fieldName }}" 
            x-model="formData['{{ $fieldName }}']"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            placeholder="{{ $placeholder }}"
        >
        @break
@endswitch

@if($helpText && $fieldType !== 'calculator')
    <p class="text-xs text-gray-500 mt-1">{{ $helpText }}</p>
@endif

@error($fieldName)
    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
@enderror

@if($calculator && !$calculator['live'])
    <button type="button" @click="calculateField(fields.find(f => f.name === '{{ $fieldName }}'))" class="mt-2 text-sm text-blue-600 hover:underline">
        {{ __('forms.calculate') }}
    </button>
@endif

</div>
