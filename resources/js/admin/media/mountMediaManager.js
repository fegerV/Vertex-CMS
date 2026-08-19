import { createApp } from 'vue';
import MediaManagerApp from './MediaManagerApp.vue';

function parseConfig(element) {
    try {
        return JSON.parse(element.dataset.config || '{}');
    } catch (error) {
        console.warn('VertexCMS media manager failed to parse config.', error);
        return {};
    }
}

export function mountMediaManager() {
    document.querySelectorAll('[data-vc-media-manager]').forEach((element) => {
        createApp(MediaManagerApp, {
            config: parseConfig(element),
            mode: 'manager',
        }).mount(element);
    });
}
