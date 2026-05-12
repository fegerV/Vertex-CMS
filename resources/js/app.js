import { createApp, ref, reactive, computed, onMounted } from 'vue';

// Expose Vue composition API globally for inline scripts (e.g., page builder)
window.Vue = { createApp, ref, reactive, computed, onMounted };

// Admin UI utilities (theme toggle, sidebar)
import './admin/app';

// Telegram Widget — lazy load only on frontend when element exists
if (typeof window !== 'undefined' && window.telegramWidgetConfig?.enabled) {
    import('./telegram-widget.js').catch(() => {
        // fail silently if module not found
    });
}
