import BaseChart from './BaseChart.js'
import { chartColors, getChartDefaults } from '../utils/ThemeChart.js'

const yFmtRp = v => 'Rp ' + Number(Math.round(v)).toLocaleString('id-ID') + '/kWh'

export default class BppChart extends BaseChart {
    constructor(canvasId) {
        super()
        this.canvasId    = canvasId
        this.hjrCanvasId = 'bppHjrComparisonChart'
    }

    /**
     * Chart utama: BPP Konsolidasi vs BPP Internal (tanpa HJR)
     */
    render(konsolidasi, internal, _hjr, labels, mode = 'trend') {
        const ctx = document.getElementById(this.canvasId)
        if (!ctx) return
        this.destroy(this.canvasId)

        const isSingle = mode === 'single'
        const defaults  = getChartDefaults(yFmtRp)

        const datasets = [
            {
                label:           'Konsolidasi',
                data:            konsolidasi,
                borderColor:     '#ff9800',
                backgroundColor: isSingle ? '#ff9800' : 'transparent',
                tension:         0.3,
                borderWidth:     2,
                pointRadius:     3,
            },
            {
                label:           'Internal',
                data:            internal,
                borderColor:     '#00ff88',
                backgroundColor: isSingle ? '#00ff88' : 'transparent',
                tension:         0.3,
                borderWidth:     2,
                pointRadius:     3,
            },
        ]

        this.charts[this.canvasId] = new Chart(ctx, {
            type: isSingle ? 'bar' : 'line',
            data: { labels, datasets },
            options: {
                ...defaults,
                plugins: {
                    ...defaults.plugins,
                    datalabels: {
                        display:   true,
                        color:     chartColors().datalabel,
                        font:      { size: 8, weight: '600' },
                        anchor:    isSingle ? 'end' : 'top',
                        align:     isSingle ? 'end' : 'top',
                        offset:    2,
                        formatter: v => v == null ? '' : yFmtRp(v),
                    },
                },
            },
        })
    }

    /**
     * Chart perbandingan BPP vs HJR — muncul di bawah chart utama.
     * Hanya relevan untuk kategori Distribusi TM (HJR TM) dan Distribusi TR (HJR TR).
     *
     * @param {number[]} bppArr     - nilai BPP konsolidasi per bulan
     * @param {number[]} hjrArr     - nilai HJR rata-rata per bulan (TR atau TM)
     * @param {string[]} labels     - label bulan
     * @param {string}   hjrLabel  - 'HJR TR' atau 'HJR TM'
     * @param {string}   mode      - 'trend' | 'single'
     */
    renderHjrComparison(bppArr, hjrArr, labels, hjrLabel = 'HJR', mode = 'trend') {
        const canvas = document.getElementById(this.hjrCanvasId)
        if (!canvas) return
        this.destroy(this.hjrCanvasId)

        // Sembunyikan chart kalau tidak ada data HJR
        if (!hjrArr || hjrArr.every(v => v === null)) {
            canvas.closest('.chart-wrapper').style.display = 'none'
            canvas.closest('.chart-wrapper')?.previousElementSibling?.classList?.add('hjr-hidden')
            return
        }
        canvas.closest('.chart-wrapper').style.display = ''

        const isSingle = mode === 'single'
        const defaults  = getChartDefaults(yFmtRp)
        const c         = chartColors()

        const datasets = [
            {
                label:           'BPP Konsolidasi',
                data:            bppArr,
                borderColor:     '#ff9800',
                backgroundColor: isSingle ? 'rgba(255,152,0,0.7)' : 'transparent',
                tension:         0.3,
                borderWidth:     2,
                pointRadius:     3,
                order:           1,
            },
            {
                label:           hjrLabel,
                data:            hjrArr,
                borderColor:     '#4fc3f7',
                backgroundColor: isSingle ? 'rgba(79,195,247,0.7)' : 'transparent',
                tension:         0.3,
                borderWidth:     2,
                borderDash:      isSingle ? [] : [5, 4],
                pointRadius:     3,
                order:           2,
            },
        ]

        this.charts[this.hjrCanvasId] = new Chart(canvas, {
            type: isSingle ? 'bar' : 'line',
            data: { labels, datasets },
            options: {
                ...defaults,
                plugins: {
                    ...defaults.plugins,
                    datalabels: {
                        display:   true,
                        color:     c.datalabel,
                        font:      { size: 8, weight: '600' },
                        anchor:    isSingle ? 'end' : 'top',
                        align:     isSingle ? 'end' : 'top',
                        offset:    2,
                        formatter: v => v == null ? '' : yFmtRp(v),
                    },
                    tooltip: {
                        ...defaults.plugins.tooltip,
                        callbacks: {
                            label: ctx => {
                                const v = ctx.raw
                                return v == null
                                    ? `${ctx.dataset.label}: —`
                                    : `${ctx.dataset.label}: Rp ${Number(Math.round(v)).toLocaleString('id-ID')}/kWh`
                            },
                        },
                    },
                },
            },
        })
    }

    destroyAll() {
        this.destroy(this.canvasId)
        this.destroy(this.hjrCanvasId)
    }
}
