import BppService from "../services/BppService.js"
import BppChart from "../charts/BppChart.js"

export default class BppModule {
    constructor(sheetsService, labels) {
        this.service = new BppService(sheetsService)
        this.chart   = new BppChart("bppComparisonChart")
        this.data    = null
        this.labels  = labels
    }

    async load() {
        this.data = await this.service.getAllBpp()
    }

    render(state) {
        if (!this.data) return
        const data = this.data[state.type]
        if (!data) return

        let kons          = [...data.konsolidasi]
        let intl          = [...data.internal]
        let displayLabels = [...this.labels]
        let mode          = "trend"

        if (state.month !== "all") {
            const index   = parseInt(state.month)
            kons          = [kons[index]]
            intl          = [intl[index]]
            displayLabels = [this.labels[index]]
            mode          = "single"
        }

        this.renderTable(kons, intl, displayLabels)
        this.chart.render(kons, intl, displayLabels, mode)
    }

    renderTable(konsolidasi, internal, labels) {
        const tableBody = document.getElementById('bppTableBody')
        if (!tableBody) return

        const bulanPenuh = {
            'Jan': 'Januari',  'Feb': 'Februari', 'Mar': 'Maret',
            'Apr': 'April',    'Mei': 'Mei',       'Jun': 'Juni',
            'Jul': 'Juli',     'Agu': 'Agustus',   'Sep': 'September',
            'Okt': 'Oktober',  'Nov': 'November',  'Des': 'Desember',
        }

        const fmt = v => v
            ? v.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
            : '—'

        tableBody.innerHTML = labels.map((label, i) => {
            const hasInternal = !!internal[i]
            const delta       = hasInternal ? (konsolidasi[i] - internal[i]) : null
           const deltaColor = delta === null ? '#888' : delta > 0 ? '#00ff88' : '#f44336'
            const deltaText   = delta === null ? '—' : (delta > 0 ? '+' : '') + fmt(delta)

            return `<tr>
                <td>${bulanPenuh[label] ?? label}</td>
                <td>${fmt(konsolidasi[i])}</td>
                <td>${hasInternal ? fmt(internal[i]) : '—'}</td>
                <td style="color:${deltaColor};">${deltaText}</td>
            </tr>`
        }).join('')
    }

    initFilters(state, onChange) {
        const filterBpp = document.getElementById("filterBpp")
        if (filterBpp) {
            filterBpp.addEventListener("change", e => { state.type = e.target.value; onChange() })
            filterBpp.value = state.type
        }
    }
}