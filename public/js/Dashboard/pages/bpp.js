import BppService from "../services/BppService.js"
import BppChart from "../charts/BppChart.js"

export default class BppPage {
    constructor(sheetsService, labels) {
        this.service = new BppService(sheetsService)
        this.chart = new BppChart("bppComparisonChart")
        this.data = null
        this.currentType = "BPP PEMBANGKIT"
        this.labels = labels
    }

    async load() {
        this.data = await this.service.getAllBpp()
    }

    render(currentMonth) {
        if (!this.data) return
        const data = this.data[this.currentType]
        if (!data) return

        let kons = [...data.konsolidasi]
        let intl = [...data.internal]
        let displayLabels = [...this.labels]
        let mode = "trend"

        if (currentMonth !== "all") {
            const index = parseInt(currentMonth)
            kons = [kons[index]]
            intl = [intl[index]]
            displayLabels = [this.labels[index]]
            mode = "single"
        }

        this.chart.render(kons, intl, displayLabels, mode)
    }

    initFilter(onRender) {
        const filterBpp = document.getElementById("filterBpp")
        if (filterBpp) {
            filterBpp.addEventListener("change", (e) => {
                this.currentType = e.target.value
                onRender()
            })
            filterBpp.value = this.currentType
        }
    }
}