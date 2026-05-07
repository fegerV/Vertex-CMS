@php
    $title = $settings['title'] ?? '';
    $description = $settings['description'] ?? '';
    $fields = $settings['fields'] ?? [];
    $buttonText = $settings['button_text'] ?? 'Submit';
    $successMessage = $settings['success_message'] ?? 'Successfully sent!';
    $actionUrl = $settings['action_url'] ?? '#';
@endphp

<div class="vc-form bg-white p-6 rounded-lg shadow-sm border border-gray-100" x-data="{ 
    submitted: false,
    loading: false,
    errors: {},
    formData: {},
    async submitForm() {
        this.loading = true;
        this.errors = {};
        
        try {
            // In a real app, you would use fetch(this.actionUrl, { method: 'POST', body: JSON.stringify(this.formData) })
            await new Promise(resolve => setTimeout(resolve, 1000));
            this.submitted = true;
        } catch (e) {
            this.errors = { general: 'Something went wrong. Please try again.' };
        } finally {
            this.loading = false;
        }
    }
}">
    @if($title)
        <h3 class="text-xl font-bold mb-2">{{ $title }}</h3>
    @endif
    
    @if($description)
        <p class="text-gray-600 mb-6">{{ $description }}</p>
    @endif

    <div x-show="submitted" class="p-4 bg-green-50 text-green-700 rounded-md mb-4">
        {{ $successMessage }}
    </div>

    <form x-show="!submitted" @submit.prevent="submitForm()" class="space-y-4">
        @foreach($fields as $field)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ $field['label'] ?? '' }}
                    @if($field['required'] ?? false) <span class="text-red-500">*</span> @endif
                </label>
                
                @if(($field['type'] ?? 'text') === 'textarea')
                    <textarea 
                        x-model="formData['{{ $field['name'] ?? '' }}']"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        rows="4"
                        @if($field['required'] ?? false) required @endif
                    ></textarea>
                @elseif(($field['type'] ?? 'text') === 'select')
                    <select 
                        x-model="formData['{{ $field['name'] ?? '' }}']"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        @if($field['required'] ?? false) required @endif
                    >
                        <option value="">Select...</option>
                        @foreach($field['options'] ?? [] as $val => $lab)
                            <option value="{{ $val }}">{{ $lab }}</option>
                        @endforeach
                    </select>
                @else
                    <input 
                        type="{{ $field['type'] ?? 'text' }}" 
                        x-model="formData['{{ $field['name'] ?? '' }}']"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        @if($field['required'] ?? false) required @endif
                    >
                @endif
            </div>
        @endforeach

        <button 
            type="submit" 
            class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50"
            :disabled="loading"
        >
            <span x-show="!loading">{{ $buttonText }}</span>
            <span x-show="loading">Sending...</span>
        </button>
    </form>
</div>
