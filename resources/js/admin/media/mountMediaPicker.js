import { createApp } from 'vue';
import MediaManagerApp from './MediaManagerApp.vue';

function readDatasetConfig(element) {
    try {
        return JSON.parse(element?.dataset?.config || '{}');
    } catch (error) {
        console.warn('VertexCMS media picker failed to parse config.', error);
        return {};
    }
}

export function mountMediaPicker(element, options = {}) {
    if (!element) {
        return null;
    }

    unmountMediaPicker(element);

    const app = createApp(MediaManagerApp, {
        config: {
            ...readDatasetConfig(element),
            ...(options.config || {}),
        },
        mode: 'picker',
        selectionKind: options.selectionKind || null,
        initialSelectedId: options.initialSelectedId ?? null,
        initialSelectedItem: options.initialSelectedItem ?? null,
        onPick: typeof options.onPick === 'function' ? options.onPick : null,
        onCloseRequest: typeof options.onCloseRequest === 'function' ? options.onCloseRequest : null,
    });

    app.mount(element);
    element.__vcMediaPickerApp = app;

    return app;
}

export function unmountMediaPicker(element) {
    if (!element?.__vcMediaPickerApp) {
        return;
    }

    element.__vcMediaPickerApp.unmount();
    delete element.__vcMediaPickerApp;
}
