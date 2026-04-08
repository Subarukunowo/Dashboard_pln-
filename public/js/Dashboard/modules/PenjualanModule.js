import PenjualanService from "../services/PenjualanService.js"
import PenjualanChart from "../charts/PenjualanChart.js"

export default class PenjualanModule {
    constructor(sheetsService, labels) {
        this.service = new PenjualanService(sheetsService)
        this.chart = new PenjualanChart("penjualanChart")
        this.data = null
        this.labels = labels
    }

    async load() {
        this.data = await this.service.getKinerjaData()
    }

    render(state) {
        if (!this.data) return
        const { view, tarif, month } = state
        let target, realisasi

        if (tarif === "JUMLAH") {
            const d = this.data[view]
            if (!d) return
            const isPercent = view.includes('persen')
            target    = isPercent ? [] : (d.target || [])
            realisasi = isPercent ? (d.realisasi || d) : (d.realisasi || [])
        } else {
            const d = this.data.perTarif?.[tarif]
            if (!d) return
            target    = d.target
            realisasi = d.realisasi
        }

        if (month !== "all") {
            const idx = parseInt(month)
            this.chart.render(
                target.length ? [target[idx]] : [],
                [realisasi[idx]],
                [this.labels[idx]], view
            )
        } else {
            this.chart.render(target, realisasi, [...this.labels], view)
        }
    }

    initFilters(state, onChange) {
        document.getElementById("filterViewPenjualan")?.addEventListener("change", e => {
            state.view = e.target.value; onChange()
        })
        document.getElementById("filterTarif")?.addEventListener("change", e => {
            state.tarif = e.target.value; onChange()
        })
    }
}