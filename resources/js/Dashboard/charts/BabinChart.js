import BaseChart from './BaseChart.js'

const themeTextColor = () =>
    document.body.classList.contains('light-mode') ? '#1e293b' : '#cccccc'

/**
 * BabinChart — override tooltip pie untuk format "X.Xjt kWh (Y%)"
 */
export default class BabinChart extends BaseChart {
    pie(id, labels, data, colors = null) {
        const fmt = (ctx) => {
            const total = ctx.dataset.data.reduce((a, b) => a + b, 0)
            const pct   = total ? ((ctx.raw / total) * 100).toFixed(1) : 0
            return `${ctx.label}: ${(ctx.raw / 1_000_000).toFixed(1)}jt kWh (${pct}%)`
        }

        const legendLabels = {
            font:          { size: 11 },
            padding:       10,
            usePointStyle: true,
            generateLabels: (chart) => {
                const color   = themeTextColor()
                const dataset = chart.data.datasets[0]
                const total   = dataset.data.reduce((a, b) => a + b, 0)
                return chart.data.labels.map((label, i) => {
                    const value  = dataset.data[i]
                    const pct    = total ? ((value / total) * 100).toFixed(1) : 0
                    const valStr = `${(value / 1_000_000).toFixed(1)}jt`
                    return {
                        text:         `${label}  ${valStr}  (${pct}%)`,
                        fillStyle:    dataset.backgroundColor[i],
                        strokeStyle:  dataset.backgroundColor[i],
                        fontColor:    color,
                        pointStyle:   'circle',
                        hidden:       false,
                        index:        i,
                        datasetIndex: 0,
                    }
                })
            },
        }

        super.pie(id, labels, data, colors ?? undefined, fmt, legendLabels)
    }
}