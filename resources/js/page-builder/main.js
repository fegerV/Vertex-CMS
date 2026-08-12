/**
 * Page Builder - Main Entry Point
 * Advanced drag-and-drop page builder with AI-powered editing
 */

import { createApp } from 'vue';
import PageBuilder from './PageBuilder.vue';
import './assets/styles.css';

// Initialize the page builder application
export function initPageBuilder(containerId, options = {}) {
    const container = document.getElementById(containerId);
    
    if (!container) {
        console.error(`Page Builder: Container #${containerId} not found`);
        return null;
    }

    const app = createApp(PageBuilder, {
        pageData: options.pageData || {},
        availableBlocks: options.availableBlocks || {},
        initialContent: options.initialContent || [],
        onSave: options.onSave || (() => {}),
        onPreview: options.onPreview || (() => {}),
        apiEndpoint: options.apiEndpoint || '/admin/api/builder',
        enableAI: options.enableAI !== false,
        aiEndpoint: options.aiEndpoint || '/admin/api/ai/edit',
        designLibrary: options.designLibrary || [],
        globalStyles: options.globalStyles || {},
        colorPalette: options.colorPalette || [],
        typographyPresets: options.typographyPresets || [],
        buttonPresets: options.buttonPresets || []
    });

    app.mount(container);
    
    console.log('Page Builder initialized successfully');
    return app;
}

// Auto-initialize if container exists
document.addEventListener('DOMContentLoaded', () => {
    const builderContainer = document.getElementById('page-builder');
    if (builderContainer && window.pageBuilderOptions) {
        initPageBuilder('page-builder', window.pageBuilderOptions);
    }
});

export { PageBuilder };
export default initPageBuilder;
