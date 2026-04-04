import PenjualanService from "../services/PenjualanService.js"
import PenjualanChart from "../charts/PenjualanChart.js"

export default class PenjualanPage {
    constructor(sheetsService, labels) {
        this.service = new PenjualanService(sheetsService)
        this.chart = new PenjualanChart("penjualanChart")
        this.data = null
        this.currentView = "bulanan"
        this.currentTarif = "JUMLAH"
        this.labels = labels
    }

    async load() {
        this.data = await this.service.getKinerjaData()
    }

    render(currentMonth) {
        if (!this.data) return

        const tarif = this.currentTarif
        const view = this.currentView
        let target, realisasi

        if (tarif === "JUMLAH") {
            const data = this.data[view]
            if (!data) return
            const isPercent = view.includes('persen')
            target = isPercent ? [] : (data.target || [])
            realisasi = isPercent ? (data.realisasi || data) : (data.realisasi || [])
        } else {
            const data = this.data.perTarif?.[tarif]
            if (!data) return
            target = data.target
            realisasi = data.realisasi
        }

        if (currentMonth !== "all") {
            const idx = parseInt(currentMonth)
            this.chart.render(
                target.length ? [target[idx]] : [],
                [realisasi[idx]],
                [this.labels[idx]], view
            )
        } else {
            this.chart.render(target, realisasi, [...this.labels], view)
        }
    }

    initFilters(onRender) {
        const filterViewPenjualan = document.getElementById("filterViewPenjualan")
        const filterTarif = document.getElementById("filterTarif")

        if (filterViewPenjualan) {
            filterViewPenjualan.addEventListener("change", (e) => {
                this.currentView = e.target.value
                onRender()
            })
            filterViewPenjualan.value = this.currentView
        }

        if (filterTarif) {
            filterTarif.addEventListener("change", (e) => {
                this.currentTarif = e.target.value
                onRender()
            })
        }
    }
}