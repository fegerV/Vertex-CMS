{{-- GDPR Cookie Banner Component --}}
<div x-data="gdprBanner()" x-show="showBanner" class="fixed bottom-0 left-0 right-0 bg-gray-900 text-white p-4 z-50 shadow-lg" style="display: none;">
    <div class="container mx-auto flex flex-col md:flex-row items-center justify-between">
        <div class="mb-4 md:mb-0 md:mr-4">
            <p class="text-sm">
                {{ config('app.name') }} использует файлы cookie для улучшения работы сайта. 
                Продолжая использовать сайт, вы соглашаетесь с нашей 
                <a href="{{ route('page.show', 'privacy-policy') }}" class="underline hover:text-gray-300" target="_blank">политикой конфиденциальности</a>.
            </p>
        </div>
        <div class="flex space-x-2">
            <button @click="acceptCookies()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm font-medium transition">
                Принять
            </button>
            <button @click="declineCookies()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm font-medium transition">
                Отклонить
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function gdprBanner() {
    return {
        showBanner: false,
        
        init() {
            const consent = localStorage.getItem('gdpr_consent');
            if (!consent) {
                this.showBanner = true;
            } else if (consent === 'accepted') {
                // Cookies accepted, enable analytics etc.
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', '{{ config("services.analytics.id", "") }}');
            }
        },
        
        acceptCookies() {
            localStorage.setItem('gdpr_consent', 'accepted');
            this.showBanner = false;
            
            // Enable analytics
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ config("services.analytics.id", "") }}');
            
            // Send consent to backend
            fetch('{{ route("api.gdpr.consent") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ consent: 'accepted' })
            });
        },
        
        declineCookies() {
            localStorage.setItem('gdpr_consent', 'declined');
            this.showBanner = false;
            
            // Send consent to backend
            fetch('{{ route("api.gdpr.consent") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ consent: 'declined' })
            });
        }
    }
}
</script>
@endpush
