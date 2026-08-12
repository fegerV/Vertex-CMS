import { createApp } from 'vue';
import FormBuilderApp from './FormBuilderApp.vue';

function parseJsonAttribute(value, fallback) {
    if (!value) {
        return fallback;
    }

    try {
        return JSON.parse(value);
    } catch (error) {
        console.warn('VertexCMS form builder failed to parse JSON payload.', error);
        return fallback;
    }
}

export function mountFormBuilder() {
    document.querySelectorAll('[data-vc-form-builder]').forEach((element) => {
        createApp(FormBuilderApp, {
            registryUrl: element.dataset.registryUrl,
            storeUrl: element.dataset.storeUrl,
            updateUrlTemplate: element.dataset.updateUrlTemplate,
            submissionsUrlTemplate: element.dataset.submissionsUrlTemplate,
            analyticsUrlTemplate: element.dataset.analyticsUrlTemplate,
            builderRouteTemplate: element.dataset.builderRouteTemplate,
            publicPreviewUrl: element.dataset.publicPreviewUrl || '',
            exitUrl: element.dataset.exitUrl,
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
            initialForm: parseJsonAttribute(
                document.getElementById(element.dataset.initialFormId)?.textContent,
                {},
            ),
        }).mount(element);
    });
}
