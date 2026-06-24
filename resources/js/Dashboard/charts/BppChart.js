import BaseChart from './BaseChart.js'
import { chartColors, getChartDefaults } from '../utils/ThemeChart.js'

export default class BppChart extends BaseChart {
    constructor(canvasId) {
        super()
        this.canvasId = canvasId
    }

    render(konsolidasi, internal, hjr, labels, mode = 'trend') {
        const ctx = document.getElementById(this.canvasId)
        if (!ctx) return
        this.destroy(this.canvasId)

        const isSingle = mode === 'single'
        const c = chartColors()
        const defaults = getChartDefaults(v => 'Rp ' + Number(Math.round(v)).toLocaleString('id-ID') + '/kWh')

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

        if (hjr) {
            datasets.push({
                label:           'HJR Rata-rata',
                data:            hjr,
                borderColor:     '#4fc3f7',
                backgroundColor: isSingle ? '#4fc3f7' : 'transparent',
                tension:         0.3,
                borderWidth:     2,
                pointRadius:     3,
                borderDash:      isSingle ? [] : [5, 5],
            })
        }

        this.charts[this.canvasId] = new Chart(ctx, {
            type: isSingle ? 'bar' : 'line',
            data: { labels, datasets },
            options: {                ...defaults,
                plugins: {
                    ...defaults.plugins,
                    datalabels: {
                        // Tampilkan di semua dataset
                        display: true,
                        color:   chartColors().datalabel,
                        font:    { size: 8, weight: '600' },
                        anchor:  isSingle ? 'end' : 'top',
                        align:   isSingle ? 'end' : 'top',
                        offset:  2,
                        formatter: v => v == null ? '' : 'Rp ' + Number(Math.round(v)).toLocaleString('id-ID'),
                    },
                },
            },
        })
    }
}
