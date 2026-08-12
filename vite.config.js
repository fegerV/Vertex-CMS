import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'path';

export default defineConfig({
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
            '@': resolve(__dirname, 'resources/js'),
            '@admin': resolve(__dirname, 'resources/js/admin'),
            '@components': resolve(__dirname, 'resources/js/components'),
        },
    },
    server: {
        host: 'localhost',
        port: 5173,
        hmr: {
            host: 'localhost',
            protocol: 'ws',
        },
    },
    build: {
        // Code splitting optimization
        rollupOptions: {
            output: {
                manualChunks: {
                    // Separate vendor chunks for better caching
                    'vendor-vue': ['vue', '@inertiajs/vue3'],
                    'vendor-tiptap': ['@tiptap/vue-3', '@tiptap/starter-kit', '@tiptap/extension-image', '@tiptap/extension-link'],
                },
                // Asset naming with content hash for long-term caching
                entryFileNames: 'assets/[name]-[hash].js',
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash].[ext]',
            },
            input: {
                // Separate entry points for admin and frontend
                styles: resolve(__dirname, 'resources/css/app.css'),
                app: resolve(__dirname, 'resources/js/app.js'),
                admin: resolve(__dirname, 'resources/js/admin/app.js'),
                builder: resolve(__dirname, 'resources/js/builder/index.js'),
            },
        },
        // Minification
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Remove console.logs in production
                drop_debugger: true,
            },
        },
        // Source maps only for debugging
        sourcemap: false,
        // CSS code splitting
        cssCodeSplit: true,
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/admin/app.js',
                'resources/js/builder/index.js',
            ],
            refresh: true,
        }),
        vue(),
    ],
});
