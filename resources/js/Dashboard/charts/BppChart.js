import BaseChart from './BaseChart.js'

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

        const datasets = [
            {
                label: 'Konsolidasi',
                data: konsolidasi,
                borderColor: '#ff9800',
                backgroundColor: isSingle ? '#ff9800' : 'transparent',
                tension: 0.3,
                borderWidth: 2,
            },
            {
                label: 'Internal',
                data: internal,
                borderColor: '#00ff88',
                backgroundColor: isSingle ? '#00ff88' : 'transparent',
                tension: 0.3,
                borderWidth: 2,
            },
        ]

        if (hjr) {
            datasets.push({
                label: 'HJR Rata-rata',
                data: hjr,
                borderColor: '#4fc3f7',
                backgroundColor: isSingle ? '#4fc3f7' : 'transparent',
                tension: 0.3,
                borderWidth: 2,
                borderDash: isSingle ? [] : [5, 5],
            })
        }

        this.charts[this.canvasId] = new Chart(ctx, {
            type: isSingle ? 'bar' : 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 300 },
                plugins: { legend: { display: true } },
            },
        })
    }
}