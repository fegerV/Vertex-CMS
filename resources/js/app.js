import { createApp, ref, reactive, computed, onMounted } from 'vue';

// Expose Vue composition API globally for inline scripts (e.g., page builder)
window.Vue = { createApp, ref, reactive, computed, onMounted };

// Admin UI utilities (theme toggle, sidebar)
import './admin/app';
import { mountBuilderPrototype } from './components/builder/mountPrototype';
import { mountFormBuilder } from './admin/forms/mountFormBuilder';

if (typeof window !== 'undefined') {
    window.Vertex = window.Vertex || {};
    window.Vertex.mountBuilderPrototype = mountBuilderPrototype;
    window.Vertex.mountFormBuilder = mountFormBuilder;
}

document.addEventListener('DOMContentLoaded', () => {
    mountBuilderPrototype();
    mountFormBuilder();
});

// Telegram Widget — lazy load only on frontend when element exists
if (typeof window !== 'undefined' && window.telegramWidgetConfig?.enabled) {
    import('./telegram-widget.js').catch(() => {
        // fail silently if module not found
    });
}
