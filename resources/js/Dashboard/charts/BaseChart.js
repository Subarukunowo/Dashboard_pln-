/**
 * BaseChart.js — Base class untuk semua chart Dashboard PLN Batam
 * Warna chart otomatis menyesuaikan tema light/dark.
 */

import { chartColors, getChartDefaults } from '../utils/ThemeChart.js'

const BASE_COLORS = ["#ff9800","#00ff88","#2196f3","#e91e63","#9c27b0","#00bcd4"]

function makeDatalabelsConfig(yFmt) {
    const c = chartColors()
    return {
        display: true,
        color:   c.datalabel,
        font:    { size: 9, weight: '600' },
        anchor:  'end',
        align:   'end',
        offset:  2,
        clip:    false,
        formatter: (value) => {
            if (value === null || value === undefined) return ''
            if (yFmt) return yFmt(value)
            const abs = Math.abs(value)
            if (abs >= 1_000_000_000) return (value / 1_000_000_000).toFixed(1) + 'M'
            if (abs >= 1_000_000)     return (value / 1_000_000).toFixed(1) + 'jt'
            if (abs >= 1_000)         return (value / 1_000).toFixed(1) + 'k'
            return Number(value).toLocaleString('id-ID', { maximumFractionDigits: 2 })
        },
    }
}

export default class BaseChart {
    constructor() { this.charts = {} }

    destroy(id) {
        if (this.charts[id]) { this.charts[id].destroy(); delete this.charts[id] }
    }

    /** Bar / Line / Area — showLabels: tampilkan angka di chart */
    make(id, type, labels, datasets, yFmt = null, showLabels = true) {
        const ctx = document.getElementById(id)
        if (!ctx) return
        this.destroy(id)

        const defaults = getChartDefaults(yFmt)

        const datalabelsCfg = showLabels ? makeDatalabelsConfig(yFmt) : { display: false }
        if (type === 'line') { datalabelsCfg.anchor = 'top'; datalabelsCfg.align = 'top' }

        this.charts[id] = new Chart(ctx, {
            type,
            data: { labels, datasets },
            // Plugin sudah diregister global via Chart.register(ChartDataLabels)
            options: {
                ...defaults,
                plugins: { ...defaults.plugins, datalabels: datalabelsCfg },
            },
        })
    }

    bar(id, labels, datasets, yFmt = null, showLabels = true) {
        this.make(id, 'bar', labels, datasets, yFmt, showLabels)
    }

    line(id, labels, datasets, yFmt = null, showLabels = true) {
        this.make(id, 'line', labels, datasets, yFmt, showLabels)
    }

    pie(id, labels, data, colors = BASE_COLORS, tooltipFmt = null, legendLabelsCfg = null) {
        const ctx = document.getElementById(id)
        if (!ctx) return
        this.destroy(id)

        const c       = chartColors()
        const isLight = document.body.classList.contains('light-mode')

        const defaultFmt = (ctx) => {
            const total = ctx.dataset.data.reduce((a, b) => a + b, 0)
            const pct   = total ? ((ctx.raw / total) * 100).toFixed(1) : 0
            return `${ctx.label}: ${ctx.raw.toLocaleString()} (${pct}%)`
        }

        // Default generateLabels — nilai + persen
        const defaultLegendLabels = {
            color:         c.legend,
            font:          { size: 11 },
            padding:       10,
            usePointStyle: true,
            generateLabels: (chart) => {
                const dataset = chart.data.datasets[0]
                const total   = dataset.data.reduce((a, b) => a + b, 0)
                return chart.data.labels.map((label, i) => {
                    const value = dataset.data[i]
                    const pct   = total ? ((value / total) * 100).toFixed(1) : 0
                    let valStr
                    if (value >= 1_000_000_000)     valStr = (value / 1_000_000_000).toFixed(1) + 'M'
                    else if (value >= 1_000_000)    valStr = (value / 1_000_000).toFixed(1) + 'jt'
                    else if (value >= 1_000)        valStr = (value / 1_000).toFixed(1) + 'k'
                    else                            valStr = Number(value).toLocaleString('id-ID', { maximumFractionDigits: 1 })
                    return {
                        text:         `${label}  ${valStr}  (${pct}%)`,
                        fillStyle:    dataset.backgroundColor[i],
                        strokeStyle:  dataset.backgroundColor[i],
                        pointStyle:   'circle',
                        hidden:       false,
                        index:        i,
                        datasetIndex: 0,
                    }
                })
            },
        }

        // Subclass boleh override legendLabelsCfg, tapi color tetap pakai theme
        const finalLegendLabels = legendLabelsCfg
            ? { ...legendLabelsCfg, color: c.legend }
            : defaultLegendLabels

        this.charts[id] = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: colors,
                    borderColor:     isLight ? '#f0f2f5' : '#1e1e1e',
                    borderWidth:     2,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                animation:  { duration: 300 },
                plugins: {
                    legend: {
                        position: 'right',
                        labels:   finalLegendLabels,
                    },
                    tooltip: {
                        backgroundColor: c.tooltip_bg,
                        titleColor: c.tooltip_fg, bodyColor: c.tooltip_fg,
                        callbacks: { label: tooltipFmt ?? defaultFmt },
                    },
                    datalabels: {
                        display: true, color: '#fff',
                        font: { size: 9, weight: '700' },
                        formatter: (value, ctx) => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0)
                            const pct = total ? ((value / total) * 100) : 0
                            return pct > 3 ? pct.toFixed(1) + '%' : ''
                        },
                    },
                },
            },
        })
    }
}
