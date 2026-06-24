import BaseChart from './BaseChart.js'

const COLORS = ["#ff9800","#00ff88","#2196f3","#e91e63","#9c27b0","#00bcd4"]

/**
 * GolTarifBatamChart — extend BaseChart
 *
 * GolTarifBatamModule memanggil:
 *   this.chart.pie(id, labels, data, colors)       ← BaseChart.pie() sudah support
 *   this.chart.bar(id, labels, datasets)            ← BaseChart.bar() alias
 *   this.chart.line(id, labels, datasets)           ← BaseChart.line() alias
 *
 * Hanya perlu override pie() untuk tooltip format Rp.
 */
export default class GolTarifBatamChart extends BaseChart {
    pie(id, labels, data, colors = COLORS) {
        const fmt = (ctx) => {
            const total = ctx.dataset.data.reduce((a, b) => a + b, 0)
            const pct   = total ? ((ctx.raw / total) * 100).toFixed(1) : 0
            return `${ctx.label}: Rp ${(ctx.raw / 1000).toFixed(2)}M (${pct}%)`
        }

        // generateLabels override untuk format Rp
        const legendLabels = {
            color:        null, // diset setelah chart terbuat
            font:         { size: 11 },
            padding:      10,
            usePointStyle: true,
            generateLabels: (chart) => {
                const dataset = chart.data.datasets[0]
                const total   = dataset.data.reduce((a, b) => a + b, 0)
                return chart.data.labels.map((label, i) => {
                    const value  = dataset.data[i]
                    const pct    = total ? ((value / total) * 100).toFixed(1) : 0
                    const valStr = `Rp ${(value / 1000).toFixed(1)}M`
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

        super.pie(id, labels, data, colors, fmt, legendLabels)
    }
}