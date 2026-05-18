import BaseChart from './BaseChart.js'

/**
 * BabinChart — override tooltip pie untuk format "X.Xjt (Y%)"
 * karena nilai babin dalam skala jutaan
 */
export default class BabinChart extends BaseChart {
    pie(id, labels, data, colors = null) {
        // Formatter khusus: tampilkan dalam jutaan
        const fmt = (ctx) => {
            const total = ctx.dataset.data.reduce((a, b) => a + b, 0)
            const pct   = total ? ((ctx.raw / total) * 100).toFixed(1) : 0
            return `${ctx.label}: ${(ctx.raw / 1_000_000).toFixed(1)}jt (${pct}%)`
        }
        super.pie(id, labels, data, colors ?? undefined, fmt)
    }
}