import { createApp } from 'vue';
import AdvancedBuilderApp from './AdvancedBuilderApp.vue';

function parseJson(value, fallback) {
    if (!value) {
        return fallback;
    }

    try {
        return JSON.parse(value);
    } catch (error) {
        console.warn('VertexCMS advanced builder failed to parse dataset JSON.', error);
        return fallback;
    }
}

export function mountAdvancedBuilder() {
    document.querySelectorAll('[data-vc-advanced-builder]').forEach((element) => {
        const page = parseJson(element.dataset.page, {});
        const config = parseJson(element.dataset.config, {});
        const initialSections = parseJson(element.dataset.initialSections, []);

        const app = createApp(AdvancedBuilderApp, {
            page,
            config,
            initialSections,
        });

        app.config.errorHandler = (error) => {
            console.error('VertexCMS advanced builder runtime error:', error);
            element.innerHTML = `
                <div class="rounded-2xl border border-rose-200 bg-rose-50 p-6 text-rose-900">
                    <h2 class="text-lg font-semibold">Builder failed to start</h2>
                    <p class="mt-2 text-sm">The advanced builder hit a frontend runtime error during initialization.</p>
                    <pre class="mt-4 overflow-auto rounded-xl bg-white p-4 text-xs text-rose-800">${String(error?.message || error)}</pre>
                </div>
            `;
        };

        app.mount(element);
    });
}
