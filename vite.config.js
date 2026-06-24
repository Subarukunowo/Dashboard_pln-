import { defineConfig } from 'vite';
import laravel          from 'laravel-vite-plugin';
import tailwindcss      from '@tailwindcss/vite';
import path             from 'path';

export default defineConfig({
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
    plugins: [
        laravel({
            input: [
                // ── App globals ───────────────────────────────────────────
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/bootstrap.js',

                // ── Dashboard & Pages ─────────────────────────────────────
                'resources/js/Dashboard/pages/dashboard.js',
                'resources/js/Dashboard/pages/excel-import.js',
                'resources/js/Dashboard/pages/fact-entry.js',
                'resources/js/Dashboard/pages/HistorisEntry.js',
                'resources/js/Dashboard/pages/HistorisImport.js',
                'resources/js/Dashboard/pages/profile.js',
                'resources/js/Dashboard/pages/settings.js',

                // ── Dashboard charts ──────────────────────────────────────
                'resources/js/Dashboard/charts/HistorisChart.js',

                // ── Dashboard components ───────────────────────────────────
                'resources/js/Dashboard/components/Chatbot.js',

                // ── Dashboard CSS ─────────────────────────────────────────
                'resources/css/layouts/navbar.css',
                'resources/css/layouts/sidebar.css',
                'resources/css/pages/dashboard.css',
                'resources/css/pages/fact-entry.css',
                'resources/css/components/dashboard-components.css',
                'resources/css/components/chatbot.css',
                'resources/css/components/app.css',
                'resources/css/components/custom-select.css',
            ],
            refresh: [
                'resources/views/**/*.blade.php',
                'resources/js/Dashboard/**/*.js',
                'resources/css/**/*.css',
            ],
        }),
        tailwindcss(),
    ],

    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },

    build: {
        chunkSizeWarningLimit: 1500,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    // ── Vendor: chart.js — berat, jarang berubah, cache lama ──
                    if (id.includes('node_modules/chart.js')) {
                        return 'vendor-chartjs'
                    }

                    // ── Vendor: sweetalert2 ───────────────────────────────────
                    if (id.includes('node_modules/sweetalert2')) {
                        return 'vendor-sweetalert'
                    }

                    // ── Vendor: lainnya (echo, pusher, axios, dll) ────────────
                    if (id.includes('node_modules')) {
                        return 'vendor'
                    }

                    // ── Dashboard cache layer ─────────────────────────────────
                    if (id.includes('Dashboard/cache/')) {
                        return 'dashboard-cache'
                    }

                    // ── Dashboard services — 1 chunk ──────────────────────────
                    if (id.includes('Dashboard/services/')) {
                        return 'dashboard-services'
                    }

                    // ── Dashboard charts — 1 chunk ────────────────────────────
                    if (id.includes('Dashboard/charts/')) {
                        return 'dashboard-charts'
                    }

                    // ── Dashboard modules — 1 chunk ───────────────────────────
                    if (id.includes('Dashboard/modules/')) {
                        return 'dashboard-modules'
                    }

                    // ── Dashboard utils & layout ──────────────────────────────
                    if (id.includes('Dashboard/utils/') || id.includes('Dashboard/layout/')) {
                        return 'dashboard-utils'
                    }

                    // Semua lain (entry points) — biarkan Vite handle sendiri
                },
            },
        },
    },
});