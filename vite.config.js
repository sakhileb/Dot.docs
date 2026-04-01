import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        // Enable asset versioning
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (!id.includes('node_modules')) {
                        return undefined;
                    }

                    if (id.includes('/quill') || id.includes('quill-image-resize-module')) {
                        return 'editor-vendor';
                    }

                    if (id.includes('/highlight.js')) {
                        return 'highlight-vendor';
                    }

                    if (id.includes('/@fortawesome/')) {
                        return 'icons-vendor';
                    }

                    if (id.includes('/alpinejs')) {
                        return 'alpine-vendor';
                    }

                    return 'vendor';
                },
                // Add hash to chunk names
                chunkFileNames: 'js/[name]-[hash].js',
                entryFileNames: 'js/[name]-[hash].js',
                assetFileNames: ({ name }) => {
                    if (/\.(gif|jpe?g|png|svg)$/.test(name ?? '')) {
                        return 'images/[name]-[hash][extname]';
                    } else if (/\.css$/.test(name ?? '')) {
                        return 'css/[name]-[hash][extname]';
                    }
                    return 'assets/[name]-[hash][extname]';
                },
            },
        },
        // Minify CSS and JS
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,
            },
        },
        // Keep actionable warnings while avoiding false alarms for expected heavy editor bundles.
        chunkSizeWarningLimit: 900,
    },
    // Enable source maps for production debugging
    sourcemap: process.env.NODE_ENV === 'production' ? 'hidden' : true,
});
