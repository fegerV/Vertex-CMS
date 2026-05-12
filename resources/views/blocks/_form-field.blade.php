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
@endphp

<div 
    class="vc-form-field mb-4" 
    :class="{ 'vc-hidden': {{ $conditional ? '!checkCondition(formData.'.$conditional['depends_on'].', \''.$conditional['operator'].'\', \''.$conditional['value'].'\')' : 'false' }} }"
    x-show="{{ $conditional ? 'checkCondition(formData.'.$conditional['depends_on'].', \''.$conditional['operator'].'\', \''.$conditional['value'].'\')' : 'true' }}"
    x-transition
>
    @if($fieldType !== 'heading' && $fieldType !== 'divider' && $fieldType !== 'html')
        <label class="vc-field-label block text-sm font-medium text-gray-700 mb-1" for="field_{{ $fieldName }}">
            {{ $fieldLabel }}
            @if($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif
@endif

@switch($fieldType)
    @case('text')
    @case('email')
    @case('tel')
    @case('number')
    @case('date')
    @case('time')
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
            <option value="">{{ $placeholder ?: 'Выберите...' }}</option>
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
            accept="{{ $options['mime_types'] ?? '' }}"
        >
        @if(isset($options['max_size']))
            <p class="text-xs text-gray-500 mt-1">Максимальный размер: {{ $options['max_size'] }} KB</p>
        @endif
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
            <div x-text="`Результат: ${formData['{{ $fieldName }}'] || '0'}`" class="mt-2 text-lg font-semibold text-blue-600"></div>
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
        Рассчитать
    </button>
@endif

</div>
