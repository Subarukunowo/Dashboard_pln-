/**
 * BaseChart.js — Base class untuk semua chart Dashboard PLN Batam
 *
 * Menyediakan method standar:
 *   make(id, type, labels, datasets, yFmt?)  — bar/line/dll
 *   pie(id, labels, data, colors?, tooltipFmt?)  — doughnut
 *   bar(id, labels, datasets, yFmt?)  — alias make dengan type='bar'
 *   line(id, labels, datasets, yFmt?)  — alias make dengan type='line'
 *   destroy(id)  — hancurkan chart sebelum re-render
 *
 * Chart turunan yang tidak punya logika khusus cukup:
 *   export default class XxxChart extends BaseChart {}
 */

const BASE_COLORS   = ["#ff9800","#00ff88","#2196f3","#e91e63","#9c27b0","#00bcd4"]
const CHART_DEFAULTS = {
    responsive:          true,
    maintainAspectRatio: false,
    animation:           { duration: 300 },
    plugins: {
        legend: { labels: { color: '#ccc', font: { size: 11 } } },
    },
    scales: {
        y: { grid: { color: '#333' }, ticks: { color: '#888' } },
        x: { grid: { display: false }, ticks: { color: '#888', font: { size: 9 }, maxRotation: 45 } },
    },
}

export default class BaseChart {
    constructor() {
        this.charts = {}
    }

    // ── Destroy sebelum re-render ─────────────────────────────
    destroy(id) {
        if (this.charts[id]) {
            this.charts[id].destroy()
            delete this.charts[id]
        }
    }

    // ── Bar / Line / Area (semua chart dengan axis) ───────────
    make(id, type, labels, datasets, yFmt = null) {
        const ctx = document.getElementById(id)
        if (!ctx) return
        this.destroy(id)

        this.charts[id] = new Chart(ctx, {
            type,
            data: { labels, datasets },
            options: {
                ...CHART_DEFAULTS,
                animation: { duration: 300 },
                scales: {
                    y: {
                        grid:  { color: '#333' },
                        ticks: { color: '#888', callback: yFmt ?? undefined },
                    },
                    x: {
                        grid:  { display: false },
                        ticks: { color: '#888', font: { size: 9 }, maxRotation: 45 },
                    },
                },
            },
        })
    }

    // ── Shorthand: bar ────────────────────────────────────────
    bar(id, labels, datasets, yFmt = null) {
        this.make(id, 'bar', labels, datasets, yFmt)
    }

    // ── Shorthand: line ───────────────────────────────────────
    line(id, labels, datasets, yFmt = null) {
        this.make(id, 'line', labels, datasets, yFmt)
    }

    /**
     * Pie / Doughnut
     * @param {string}   id
     * @param {string[]} labels
     * @param {number[]} data
     * @param {string[]} [colors]      — override warna default
     * @param {Function} [tooltipFmt]  — custom tooltip label callback
     *                                    fn(ctx) → string
     *                                    default: "{label}: {value.toLocaleString()} ({pct}%)"
     */
    pie(id, labels, data, colors = BASE_COLORS, tooltipFmt = null) {
        const ctx = document.getElementById(id)
        if (!ctx) return
        this.destroy(id)

        const defaultFmt = (ctx) => {
            const total = ctx.dataset.data.reduce((a, b) => a + b, 0)
            const pct   = total ? ((ctx.raw / total) * 100).toFixed(1) : 0
            return `${ctx.label}: ${ctx.raw.toLocaleString()} (${pct}%)`
        }

        this.charts[id] = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: colors,
                    borderColor:     '#1e1e1e',
                    borderWidth:     2,
                }],
            },
            options: {
                responsive:          true,
                maintainAspectRatio: false,
                animation:           { duration: 300 },
                plugins: {
                    legend: {
                        position: 'right',
                        labels:   { color: '#ccc', font: { size: 11 } },
                    },
                    tooltip: {
                        callbacks: { label: tooltipFmt ?? defaultFmt },
                    },
                },
            },
        })
    }
}