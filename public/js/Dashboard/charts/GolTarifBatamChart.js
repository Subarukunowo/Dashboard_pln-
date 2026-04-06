export default class GolTarifBatamChart {
    constructor() { this.charts = {} }

    destroy(id) {
        if (this.charts[id]) { this.charts[id].destroy(); delete this.charts[id] }
    }

    pie(id, labels, data, colors) {
        const ctx = document.getElementById(id)
        if (!ctx) return
        this.destroy(id)
        this.charts[id] = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{ data, backgroundColor: colors, borderColor: '#1e1e1e', borderWidth: 2 }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { color: '#ccc', font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: ctx => {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0)
                                const pct = total ? ((ctx.raw / total) * 100).toFixed(1) : 0
                                return `${ctx.label}: Rp ${(ctx.raw/1000).toFixed(2)}M (${pct}%)`
                            }
                        }
                    }
                }
            }
        })
    }

    bar(id, labels, datasets, yFmt = null) {
        const ctx = document.getElementById(id)
        if (!ctx) return
        this.destroy(id)
        this.charts[id] = new Chart(ctx, {
            type: 'bar',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#ccc', font: { size: 11 } } } },
                scales: {
                    y: { grid: { color: '#333' }, ticks: { color: '#888', callback: yFmt || undefined } },
                    x: { grid: { display: false }, ticks: { color: '#888' } }
                }
            }
        })
    }

    line(id, labels, datasets) {
        const ctx = document.getElementById(id)
        if (!ctx) return
        this.destroy(id)
        this.charts[id] = new Chart(ctx, {
            type: 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#ccc', font: { size: 11 } } } },
                scales: {
                    y: { grid: { color: '#333' }, ticks: { color: '#888' } },
                    x: { grid: { display: false }, ticks: { color: '#888' } }
                }
            }
        })
    }
}