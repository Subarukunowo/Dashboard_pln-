import BppService from "../services/BppService.js"
import BppChart from "../charts/BppChart.js"

export default class BppModule {
    constructor(sheetsService, labels) {
        this.service = new BppService(sheetsService)
        this.chart = new BppChart("bppComparisonChart")
        this.data = null
        this.labels = labels
    }

    async load() {
        this.data = await this.service.getAllBpp()
    }

    // Method baru: Handle rendering tabel secara mandiri
    renderTable(konsolidasi, internal, labels) {
        const tableBody = document.getElementById('bppTableBody')
        if (!tableBody) return

        tableBody.innerHTML = labels.map((label, i) => {
            const delta      = konsolidasi[i] - internal[i]
            const deltaColor = delta >= 0 ? '#f44336' : '#00ff88'
            const fmt        = v => v ? v.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—'
            
            // Layout centered sesuai permintaan sebelumnya
            return `<tr class="bpp-row">
                <td class="bpp-bulan" style="text-align:center;">${label}</td>
                <td class="bpp-val kons" style="text-align:center;">${fmt(konsolidasi[i])}</td>
                <td class="bpp-val intl" style="text-align:center;">${internal[i] ? fmt(internal[i]) : '—'}</td>
                <td class="bpp-val" style="text-align:center; color:${deltaColor}">
                    ${delta !== 0 ? (delta > 0 ? '+' : '') + fmt(delta) : '—'}
                </td>
            </tr>`
        }).join('')
    }

    render(state) {
        if (!this.data) return
        const data = this.data[state.type]
        if (!data) return

        let kons = [...data.konsolidasi]
        let intl = [...data.internal]
        let displayLabels = [...this.labels]
        let mode = "trend"

        if (state.month !== "all") {
            const index = parseInt(state.month)
            kons = [kons[index]]
            intl = [intl[index]]
            displayLabels = [this.labels[index]]
            mode = "single"
        }

        // 1. Render tabel dulu (sekarang di-handle module)
        this.renderTable(kons, intl, displayLabels)
        
        // 2. Render chart
        this.chart.render(kons, intl, displayLabels, mode)
    }

    initFilters(state, onChange) {
        const filterBpp = document.getElementById("filterBpp")
        if (filterBpp) {
            filterBpp.addEventListener("change", e => { state.type = e.target.value; onChange() })
            filterBpp.value = state.type
        }
    }
}