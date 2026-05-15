import { createApp, ref, reactive, computed, onMounted } from 'vue';

// Expose Vue composition API globally for inline scripts (e.g., page builder)
window.Vue = { createApp, ref, reactive, computed, onMounted };

// Admin UI utilities (theme toggle, sidebar)
import './admin/app';
import { mountBuilderPrototype } from './components/builder/mountPrototype';
import { mountAdvancedBuilder } from './admin/builder/mountAdvancedBuilder';
import { mountFormBuilder } from './admin/forms/mountFormBuilder';
import { mountMediaManager } from './admin/media/mountMediaManager';
import { mountMediaPicker, unmountMediaPicker } from './admin/media/mountMediaPicker';

if (typeof window !== 'undefined') {
    window.Vertex = window.Vertex || {};
    window.Vertex.mountBuilderPrototype = mountBuilderPrototype;
    window.Vertex.mountAdvancedBuilder = mountAdvancedBuilder;
    window.Vertex.mountFormBuilder = mountFormBuilder;
    window.Vertex.mountMediaManager = mountMediaManager;
    window.Vertex.mountMediaPicker = mountMediaPicker;
    window.Vertex.unmountMediaPicker = unmountMediaPicker;
}

document.addEventListener('DOMContentLoaded', () => {
    mountBuilderPrototype();
    mountAdvancedBuilder();
    mountFormBuilder();
    mountMediaManager();
});

// Telegram Widget — lazy load only on frontend when element exists
if (typeof window !== 'undefined' && window.telegramWidgetConfig?.enabled) {
    import('./telegram-widget.js').catch(() => {
        // fail silently if module not found
    });
}
