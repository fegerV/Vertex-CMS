@php
    $title = $settings['title'] ?? 'Свяжитесь с нами';
    $description = $settings['description'] ?? '';
    $fields = $settings['fields'] ?? [
        ['type' => 'text', 'name' => 'name', 'label' => 'Имя', 'required' => true],
        ['type' => 'email', 'name' => 'email', 'label' => 'Email', 'required' => true],
        ['type' => 'textarea', 'name' => 'message', 'label' => 'Сообщение', 'required' => true],
    ];
    $buttonText = $settings['button_text'] ?? 'Отправить';
    $successMessage = $settings['success_message'] ?? 'Сообщение успешно отправлено!';
    $actionUrl = $settings['action_url'] ?? '/api/contact';
@endphp

<div class="pb-form">
    <h3 class="pb-form__title">{{ $title }}</h3>
    @if($description)
        <p class="pb-form__description">{{ $description }}</p>
    @endif
    
    <form 
        action="{{ $actionUrl }}" 
        method="POST" 
        class="pb-form__element"
        data-success-message="{{ $successMessage }}"
    >
        @csrf
        
        @foreach($fields as $field)
            <div class="pb-form__field">
                @if($field['type'] !== 'textarea')
                    <label for="{{ $field['name'] }}" class="pb-form__label">
                        {{ $field['label'] }}
                        @if($field['required'] ?? false) <span class="pb-form__required">*</span> @endif
                    </label>
                    <input 
                        type="{{ $field['type'] }}" 
                        id="{{ $field['name'] }}" 
                        name="{{ $field['name'] }}"
                        class="pb-form__input"
                        @if($field['required'] ?? false) required @endif
                    />
                @else
                    <label for="{{ $field['name'] }}" class="pb-form__label">
                        {{ $field['label'] }}
                        @if($field['required'] ?? false) <span class="pb-form__required">*</span> @endif
                    </label>
                    <textarea 
                        id="{{ $field['name'] }}" 
                        name="{{ $field['name'] }}"
                        class="pb-form__textarea"
                        rows="4"
                        @if($field['required'] ?? false) required @endif
                    ></textarea>
                @endif
            </div>
        @endforeach
        
        <button type="submit" class="pb-button pb-button--primary pb-form__submit">
            {{ $buttonText }}
        </button>
    </form>
</div>
