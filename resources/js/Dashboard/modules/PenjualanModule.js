import PenjualanService from "../services/PenjualanService.js"
import PenjualanChart from "../charts/PenjualanChart.js"

export default class PenjualanModule {
    constructor(sheetsService, labels) {
        this.service = new PenjualanService(sheetsService)
        this.chart   = new PenjualanChart("penjualanChart")
        this.data    = null
        this.labels  = labels
    }

    async load() {
        this.data = await this.service.getKinerjaData()
    }

    #getLabels() {
        return [...document.querySelectorAll("#filterBulanPenjualan option")]
            .filter(o => o.value !== "all")
            .map(o => o.textContent.trim())
    }

    render(state) {
        if (!this.data) return
        const { view, tarif, month } = state
        const labels = this.#getLabels()
        let target, realisasi, displayLabels

        if (tarif === "JUMLAH") {
            const d = this.data[view]
            if (!d) return
            const isPercent = view.includes('persen')
            target        = isPercent ? [] : (d.target    || [])
            realisasi     = isPercent ? (d.realisasi || d) : (d.realisasi || [])
            displayLabels = [...labels]
        } else {
            const d = this.data.perTarif?.[tarif]
            if (!d) return
            target        = d.target
            realisasi     = d.realisasi
            displayLabels = [...labels]
        }

        if (month !== "all") {
            const idx             = parseInt(month)
            const slicedTarget    = target.length ? [target[idx]] : []
            const slicedRealisasi = [realisasi[idx]]
            const slicedLabels    = [labels[idx]]

            this.chart.render(slicedTarget, slicedRealisasi, slicedLabels, view)
            this.#renderTable(slicedLabels, slicedTarget, slicedRealisasi)
        } else {
            this.chart.render(target, realisasi, displayLabels, view)
            this.#renderTable(displayLabels, target, realisasi)
        }
    }

    initFilters(state, onChange) {
        document.getElementById("filterViewPenjualan")?.addEventListener("change",  e => { state.view  = e.target.value; onChange() })
        document.getElementById("filterTarif")?.addEventListener("change",          e => { state.tarif = e.target.value; onChange() })
        document.getElementById("filterBulanPenjualan")?.addEventListener("change", e => { state.month = e.target.value; onChange() })
    }

    #renderTable(labels, target, realisasi) {
        const tbody = document.getElementById("penjualanTableBody")
        if (!tbody) return

        const isPercent = realisasi.length > 0 &&
            (typeof realisasi[0] === "number" && realisasi[0] <= 100 && realisasi[0] >= 0)

        const fmt = v => {
            if (v === null || v === undefined) return "—"
            return isPercent
                ? (v * 100).toFixed(2) + "%"
                : (v / 1_000_000).toFixed(2) + " jt"
        }

        tbody.innerHTML = labels.map((bln, i) => {
            const t     = target[i]    ?? 0
            const r     = realisasi[i] ?? 0
            const d     = r - t
            const color = d >= 0 ? "var(--accent-green)" : "#f44336"
            return `<tr>
                <td>${bln}</td>
                <td style="color:var(--accent-orange);">${fmt(t)}</td>
                <td style="color:var(--accent-green);">${fmt(r)}</td>
                <td style="color:${color}; font-weight:600;">${d !== 0 ? (d >= 0 ? "+" : "") + fmt(d) : "—"}</td>
            </tr>`
        }).join("")
    }
}