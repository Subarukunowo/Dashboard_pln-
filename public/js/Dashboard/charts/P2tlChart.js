export default class P2tlChart {
    constructor() { this.charts = {} }
    destroy(id) { if (this.charts[id]) { this.charts[id].destroy(); delete this.charts[id] } }
    make(id, type, labels, datasets, yFmt = null) {
        const ctx = document.getElementById(id)
        if (!ctx) return
        this.destroy(id)
        this.charts[id] = new Chart(ctx, {
            type, data: { labels, datasets },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#ccc', font: { size: 11 } } } },
                scales: {
                    y: { grid: { color: '#333' }, ticks: { color: '#888', callback: yFmt || undefined } },
                    x: { grid: { display: false }, ticks: { color: '#888' } }
                }
            }
        })
    }
}