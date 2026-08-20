@php
    $settings = \App\Models\GdprSetting::getActive();
@endphp

@if($settings->enabled)
<div id="cookie-consent-banner" 
     class="fixed bottom-0 left-0 right-0 z-50 bg-gray-900 text-white p-4 md:p-6 transform transition-transform duration-300 ease-in-out {{ request()->cookie('gdpr_accepted') ? 'translate-y-full' : 'translate-y-0' }}"
     style="{{ request()->cookie('gdpr_accepted') ? 'display: none;' : '' }}"
     data-cookie-duration="{{ $settings->cookie_duration_days }}">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            {{-- Content --}}
            <div class="flex-1 text-center md:text-left">
                <h3 class="text-lg font-semibold mb-2">{{ $settings->banner_title }}</h3>
                <p class="text-sm text-gray-300">
                    {{ $settings->banner_message }}
                    @if($settings->policy_link)
                        <a href="{{ $settings->policy_link }}" 
                           target="_blank" 
                           class="text-blue-400 hover:text-blue-300 underline ml-1">
                            Политика конфиденциальности
                        </a>
                    @endif
                </p>
            </div>

            {{-- Buttons --}}
            <div class="flex flex-wrap items-center justify-center gap-3 flex-shrink-0">
                <button onclick="declineCookies()" 
                        class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors">
                    {{ $settings->decline_button_text }}
                </button>
                <button onclick="acceptCookies()" 
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-500 rounded-lg transition-colors">
                    {{ $settings->accept_button_text }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    const banner = document.getElementById('cookie-consent-banner');
    const cookieDuration = parseInt(banner.dataset.cookieDuration) || 365;

    function setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/;SameSite=Lax';
    }

    function getCookie(name) {
        const nameEQ = name + '=';
        const ca = document.cookie.split(';');
        for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    window.acceptCookies = function() {
        // Set consent cookie
        setCookie('gdpr_accepted', 'true', cookieDuration);
        setCookie('gdpr_consent_date', new Date().toISOString(), cookieDuration);
        
        // Enable analytics/tracking scripts here if needed
        enableTrackingScripts();
        
        // Hide banner with animation
        banner.classList.add('translate-y-full');
        setTimeout(() => {
            banner.style.display = 'none';
        }, 300);

        // Dispatch custom event for other scripts to listen
        document.dispatchEvent(new CustomEvent('cookiesAccepted', { detail: { type: 'full' } }));
    };

    window.declineCookies = function() {
        // Set minimal cookie to prevent showing banner again
        setCookie('gdpr_accepted', 'minimal', 30);
        setCookie('gdpr_consent_date', new Date().toISOString(), 30);
        
        // Hide banner with animation
        banner.classList.add('translate-y-full');
        setTimeout(() => {
            banner.style.display = 'none';
        }, 300);

        // Dispatch custom event
        document.dispatchEvent(new CustomEvent('cookiesDeclined', {}));
    };

    function enableTrackingScripts() {
        // Load Google Analytics, Facebook Pixel, etc. only after consent
        // Example:
        // loadScript('https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID');
        
        console.log('[Cookie Consent] Tracking scripts enabled');
    }

    function loadScript(src) {
        const script = document.createElement('script');
        script.src = src;
        script.async = true;
        document.head.appendChild(script);
        return script;
    }

    // Check if already accepted
    const existingConsent = getCookie('gdpr_accepted');
    if (existingConsent === 'true') {
        banner.style.display = 'none';
        enableTrackingScripts();
    } else if (existingConsent === 'minimal') {
        banner.style.display = 'none';
    }

    // Make functions globally available
    window.cookieConsent = {
        accept: window.acceptCookies,
        decline: window.declineCookies,
        getStatus: () => getCookie('gdpr_accepted'),
    };
})();
</script>
@endif
