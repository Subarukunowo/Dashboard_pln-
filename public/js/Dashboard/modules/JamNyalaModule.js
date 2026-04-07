const LABELS = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]
const COLORS = ["#ff9800","#00ff88","#2196f3","#e91e63","#9c27b0","#00bcd4"]

export default class JamNyalaModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return

        const selected = state.tarif === "ALL"
            ? data.tarif.filter(t => t.toLowerCase().includes("jumlah"))
            : [state.tarif]

        this.chart.make("chartJamNyalaLine", "line", LABELS,
            selected.map((t, i) => ({
                label: t,
                data: data.data[t] || new Array(12).fill(0),
                borderColor: COLORS[i % COLORS.length],
                tension: 0.4, fill: false, pointRadius: 3
            }))
        )

        const tarifBar = state.tarif === "ALL" ? (selected[0] || data.tarif[0]) : state.tarif
        const d = data.data[tarifBar] || new Array(12).fill(0)
        this.chart.make("chartJamNyalaBar", "bar", LABELS, [
            { label: tarifBar, data: d, backgroundColor: "#00ff88" }
        ], v => v.toFixed(0) + " jam")

        this._populateTarif(data, state.tarif)
    }

    initFilters(state, onChange) {
        document.getElementById("filterTarifJamNyala")?.addEventListener("change", e => {
            state.tarif = e.target.value; onChange()
        })
    }

    _populateTarif(data, currentTarif) {
        const sel = document.getElementById("filterTarifJamNyala")
        if (sel && sel.options.length === 0 && data.tarif) {
            const opt0 = document.createElement("option")
            opt0.value = "ALL"; opt0.textContent = "Semua Golongan"
            sel.appendChild(opt0)
            data.tarif.forEach(t => {
                const opt = document.createElement("option")
                opt.value = t; opt.textContent = t
                sel.appendChild(opt)
            })
            sel.value = currentTarif
        }
    }
}