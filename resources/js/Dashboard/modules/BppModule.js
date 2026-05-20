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

    #getLabels() {
        return [...document.querySelectorAll("#filterBulanBpp option")]
            .filter(o => o.value !== "all")
            .map(o => o.textContent.trim())
    }

    render(state) {
        if (!this.data) return
        const data = this.data[state.type]
        if (!data) return

        let kons          = [...data.konsolidasi]
        let intl          = [...data.internal]
        let displayLabels = this.#getLabels()
        let mode          = "trend"

        if (state.month !== "all") {
            const index   = parseInt(state.month)
            kons          = [kons[index]]
            intl          = [intl[index]]
            displayLabels = [displayLabels[index]]
            mode          = "single"
        }

        this.#renderTable(kons, intl, displayLabels)
        this.chart.render(kons, intl, displayLabels, mode)
    }

    initFilters(state, onChange) {
        document.getElementById("filterBpp")?.addEventListener("change",      e => { state.type  = e.target.value; onChange() })
        document.getElementById("filterBulanBpp")?.addEventListener("change", e => { state.month = e.target.value; onChange() })

        const filterBpp = document.getElementById("filterBpp")
        if (filterBpp) filterBpp.value = state.type
    }

    #renderTable(konsolidasi, internal, labels) {
        const tbody = document.getElementById("bppTableBody")
        if (!tbody) return

        const fmt = v => v
            ? v.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
            : '—'

        tbody.innerHTML = labels.map((label, i) => {
            const hasInternal = !!internal[i]
            const delta       = hasInternal ? (konsolidasi[i] - internal[i]) : null
            const deltaColor  = delta === null ? '#888' : delta > 0 ? '#00ff88' : '#f44336'
            const deltaText   = delta === null ? '—' : (delta > 0 ? '+' : '') + fmt(delta)

            return `<tr>
                <td>${label}</td>
                <td>${fmt(konsolidasi[i])}</td>
                <td>${hasInternal ? fmt(internal[i]) : '—'}</td>
                <td style="color:${deltaColor};">${deltaText}</td>
            </tr>`
        }).join('')
    }
}