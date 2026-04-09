const LABELS = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]
const COLORS = ["#ff9800","#00ff88","#2196f3","#e91e63","#9c27b0","#00bcd4"]

export default class JamNyalaModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return

        const sliceIdx = (state.bulan && state.bulan !== "all")
            ? parseInt(state.bulan) + 1 : 12
        const labels = LABELS.slice(0, sliceIdx)

        const selected = state.tarif === "ALL"
            ? data.tarif.filter(t => t.toLowerCase().includes("jumlah"))
            : [state.tarif]

        this.chart.make("chartJamNyalaLine", "line", labels,
            selected.map((t, i) => ({
                label: t,
                data: (data.data[t] || new Array(12).fill(0)).slice(0, sliceIdx),
                borderColor: COLORS[i % COLORS.length],
                tension: 0.4, fill: false, pointRadius: 3
            }))
        )

        const tarifBar = state.tarif === "ALL" ? (selected[0] || data.tarif[0]) : state.tarif
        const d = (data.data[tarifBar] || new Array(12).fill(0)).slice(0, sliceIdx)
        this.chart.make("chartJamNyalaBar", "bar", labels, [
            { label: tarifBar, data: d, backgroundColor: "#00ff88" }
        ], v => v.toFixed(0) + " jam")

        this._renderSideTable(labels, d)
        this._populateTarif(data, state.tarif)
    }

    initFilters(state, onChange) {
        document.getElementById("filterTarifJamNyala")?.addEventListener("change", e => {
            state.tarif = e.target.value; onChange()
        })
        document.getElementById("filterBulanJamNyala")?.addEventListener("change", e => {
            state.bulan = e.target.value; onChange()
        })
    }

    _renderSideTable(labels, d) {
        const tbody = document.getElementById("jamNyalaSideTable")
        if (!tbody) return
        tbody.innerHTML = labels.map((bln, i) => `<tr>
            <td style="color:#888; font-size:0.75rem;">${bln}</td>
            <td style="text-align:right; color:var(--accent-green);">${(d[i] || 0).toFixed(0)} jam</td>
        </tr>`).join("")
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