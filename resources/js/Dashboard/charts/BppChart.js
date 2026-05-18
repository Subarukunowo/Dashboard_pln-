import BaseChart from './BaseChart.js'

export default class BppChart extends BaseChart {
    constructor(canvasId) {
        super()
        this.canvasId = canvasId
    }

    render(konsolidasi, internal, labels, mode = 'trend') {
        const ctx = document.getElementById(this.canvasId)
        if (!ctx) return


        this.destroy(this.canvasId)

        const isSingle = mode === 'single'
        this.charts[this.canvasId] = new Chart(ctx, {
            type: isSingle ? 'bar' : 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'Konsolidasi',
                        data: konsolidasi,
                        borderColor: '#ff9800',
                        backgroundColor: isSingle ? '#ff9800' : 'transparent',
                        tension: 0.3,
                    },
                    {
                        label: 'Internal',
                        data: internal,
                        borderColor: '#00ff88',
                        backgroundColor: isSingle ? '#00ff88' : 'transparent',
                        tension: 0.3,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 300 },
                plugins: { legend: { display: false } },
            },
        })
    }
   
}