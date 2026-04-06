export default class PelangganChart {
    constructor() { this.charts = {} }

    destroy(id) {
        if (this.charts[id]) { this.charts[id].destroy(); delete this.charts[id] }
    }

    make(id, type, labels, datasets, yFmt = null) {
        const ctx = document.getElementById(id)
        if (!ctx) return
        this.destroy(id)
        this.charts[id] = new Chart(ctx, {
            type,
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

    pie(id, labels, data) {
        const COLORS = ["#ff9800","#00ff88","#2196f3","#e91e63","#9c27b0","#00bcd4"]
        const ctx = document.getElementById(id)
        if (!ctx) return
        this.destroy(id)
        this.charts[id] = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{ data, backgroundColor: COLORS, borderColor: '#1e1e1e', borderWidth: 2 }]
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
                                return `${ctx.label}: ${ctx.raw.toLocaleString()} (${pct}%)`
                            }
                        }
                    }
                }
            }
        })
    }
}