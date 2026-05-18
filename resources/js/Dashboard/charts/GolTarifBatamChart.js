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
        super.pie(id, labels, data, colors, fmt)
    }
}