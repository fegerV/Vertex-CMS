import { createApp } from 'vue';
import DesignLibraryApp from './DesignLibraryApp.vue';

function parseJson(value, fallback) {
    if (!value) {
        return fallback;
    }

    try {
        return JSON.parse(value);
    } catch (error) {
        console.warn('VertexCMS design library failed to parse dataset JSON.', error);
        return fallback;
    }
}

export function mountDesignLibrary() {
    document.querySelectorAll('[data-vc-design-library]').forEach((element) => {
        const workspace = parseJson(element.dataset.workspace, {});
        const apiUrl = element.dataset.apiUrl || '/admin/api/pages/builder/design-library';

        const app = createApp(DesignLibraryApp, {
            workspace,
            apiUrl,
        });

        app.config.errorHandler = (error) => {
            console.error('VertexCMS design library runtime error:', error);
            element.innerHTML = `
                <div class="rounded-2xl border border-rose-200 bg-rose-50 p-6 text-rose-900">
                    <h2 class="text-lg font-semibold">Design Library failed to start</h2>
                    <p class="mt-2 text-sm">The design library hit a frontend runtime error during initialization.</p>
                    <pre class="mt-4 overflow-auto rounded-xl bg-white p-4 text-xs text-rose-800">${String(error?.message || error)}</pre>
                </div>
            `;
        };

        app.mount(element);
    });
}
