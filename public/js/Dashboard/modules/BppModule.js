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