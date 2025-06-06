import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources',
            vue: 'vue/dist/vue.esm-bundler.js',
        },
    },
    optimizeDeps: {
        include: ['vue', 'vue-router', 'pinia'],
        force: true,
    },
    build: {
        rollupOptions: {
            output: {
                entryFileNames: `[name].[hash].js`,
                chunkFileNames: `[name].[hash].js`,
                assetFileNames: `[name].[hash].[ext]`
            }
        },
        chunkSizeWarningLimit: 1000,
    },
    server: {
        hmr: {
            host: 'localhost',
        },
        warmup: {
            clientFiles: [
                './resources/js/app.js',
                './resources/js/router/index.js',
                './resources/js/stores/**/*.js',
            ],
        },
    },
});