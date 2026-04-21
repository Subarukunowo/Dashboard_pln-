import BaseChart from './BaseChart.js'

/**
 * BppChart — chart khusus BPP dengan render() + renderTable()
 * Extend BaseChart untuk mewarisi destroy() dan helper make() internal
 */
export default class BppChart extends BaseChart {
    constructor(canvasId) {
        super()
        this.canvasId = canvasId
    }

    render(konsolidasi, internal, labels, mode = 'trend') {
        const ctx = document.getElementById(this.canvasId)
        if (!ctx) return

        this.renderTable(konsolidasi, internal, labels)

        // Gunakan destroy dari BaseChart
        this.destroy(this.canvasId)

        const isSingle = mode === 'single'
        this.charts[this.canvasId] = new Chart(ctx, {
            type: isSingle ? 'bar' : 'line',
            data: {
                labels,
                datasets: [
                    {
                        label:           'Konsolidasi',
                        data:            konsolidasi,
                        borderColor:     '#ff9800',
                        backgroundColor: isSingle ? '#ff9800' : 'transparent',
                        tension:         0.3,
                    },
                    {
                        label:           'Internal',
                        data:            internal,
                        borderColor:     '#00ff88',
                        backgroundColor: isSingle ? '#00ff88' : 'transparent',
                        tension:         0.3,
                    },
                ],
            },
            options: {
                responsive:          true,
                maintainAspectRatio: false,
                animation:           { duration: 300 },
                plugins: {
                    legend: { display: false },
                },
            },
        })
    }

    renderTable(konsolidasi, internal, labels) {
        const tableBody = document.getElementById('bppTableBody')
        if (!tableBody) return

        tableBody.innerHTML = labels.map((label, i) => {
            const delta      = konsolidasi[i] - internal[i]
            const deltaColor = delta >= 0 ? '#f44336' : '#00ff88'
            const fmt        = v => v ? v.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—'
            return `<tr class="bpp-row">
                <td class="bpp-bulan">${label}</td>
                <td class="bpp-val kons">${fmt(konsolidasi[i])}</td>
                <td class="bpp-val intl">${internal[i] ? fmt(internal[i]) : '—'}</td>
                <td class="bpp-val" style="color:${deltaColor}">
                    ${delta ? (delta > 0 ? '+' : '') + fmt(delta) : '—'}
                </td>
            </tr>`
        }).join('')
    }
}