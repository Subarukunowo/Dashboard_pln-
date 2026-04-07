const COLORS = ["#ff9800","#00ff88","#2196f3","#e91e63","#9c27b0","#00bcd4"]
const LABELS = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]

export default class GolTarifBatamModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return
        const akum = state.view === "akumulasi"
        const idx = parseInt(state.bulan)
        const is2024 = state.tahun === "2024"

        const nilaiGol = data.golongan.map(g =>
            is2024
                ? (akum ? data.realisasi2024Akum[g][idx] : data.realisasi2024Bul[g][idx])
                : (akum ? data.target2025Akum[g][idx]    : data.target2025Bul[g][idx])
        )

        const judulEl = document.getElementById("judulPie")
        if (judulEl) judulEl.textContent =
            (is2024 ? "Realisasi" : "Target") + " Pendapatan " + state.tahun +
            (akum ? " (Akumulasi)" : " (Bulanan)") + " — " + LABELS[idx]

        this.chart.pie("chartPieTarif", data.golongan.map(g => g.replace(/ \(.\) ?/, '')), nilaiGol, COLORS)

        this.chart.bar("chartRealisasiBulanan", LABELS, [
            { label: "Target",    data: data.realisasiBulanan.map(d => d.target),    backgroundColor: "#ff9800" },
            { label: "Realisasi", data: data.realisasiBulanan.map(d => d.realisasi), backgroundColor: "#00ff88" },
        ])
        const delta = data.realisasiBulanan.map(d => d.delta)
        this.chart.bar("chartDeltaRealisasi", LABELS, [
            { label: "Delta", data: delta, backgroundColor: delta.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ])

        const t = data.total
        this.chart.bar("chartTotal2024vs2025", t.map(d => d.bulan), [
            { label: "Target 2024",    data: t.map(d => d.target2024),    backgroundColor: "#555" },
            { label: "Realisasi 2024", data: t.map(d => d.realisasi2024), backgroundColor: "#2196f3" },
            { label: "Target 2025",    data: t.map(d => d.target2025),    backgroundColor: "#ff9800" },
            { label: "Realisasi 2025", data: t.map(d => d.realisasi2025), backgroundColor: "#00ff88" },
        ])
        const deltaTotal = t.map(d => d.deltaKwh)
        this.chart.bar("chartTotalDelta", t.map(d => d.bulan), [
            { label: "Delta", data: deltaTotal, backgroundColor: deltaTotal.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ])
        this.chart.line("chartTotalPertumbuhan", t.map(d => d.bulan), [
            { label: "% Target",    data: t.map(d => ((d.target2025/d.target2024)-1)*100),       borderColor: "#ff9800", tension: 0.4, fill: false, pointRadius: 3 },
            { label: "% Realisasi", data: t.map(d => ((d.realisasi2025/d.realisasi2024)-1)*100), borderColor: "#00ff88", tension: 0.4, fill: false, pointRadius: 3 },
        ])

        this._tables(data, t)
    }

    initFilters(state, onChange) {
        document.getElementById("filterTahunPie")?.addEventListener("change", e => { state.tahun = e.target.value; onChange() })
        document.getElementById("filterViewPie")?.addEventListener("change",  e => { state.view  = e.target.value; onChange() })
        document.getElementById("filterBulanPie")?.addEventListener("change", e => { state.bulan = e.target.value; onChange() })
    }

    _tables(data, t) {
        const tbodyR = document.getElementById("tabelRealisasi")
        if (tbodyR) {
            const fmt = v => "Rp " + (v/1000).toFixed(2) + " Jt"
            const fmtPct = v => (v * 100).toFixed(2) + "%"
            tbodyR.innerHTML = data.realisasiBulanan.map(d => `<tr>
                <td>${d.bulan}</td>
                <td>${fmt(d.target)}</td>
                <td>${fmt(d.realisasi)}</td>
                <td style="color:${d.delta >= 0 ? '#00ff88' : '#f44336'}">${fmt(d.delta)}</td>
                <td style="color:${d.delta >= 0 ? '#00ff88' : '#f44336'}">${fmtPct(d.pencapaian)}</td>
            </tr>`).join("")
        }
        const tbodyT = document.getElementById("tabelTotal")
        if (tbodyT) {
            const fmtT = v => "Rp " + (v/1_000_000_000).toFixed(2) + "T"
            const fmtPct = v => (v * 100).toFixed(2) + "%"
            tbodyT.innerHTML = t.map(d => `<tr>
                <td>${d.bulan}</td>
                <td>${fmtT(d.target2024)}</td>
                <td>${fmtT(d.realisasi2024)}</td>
                <td>${fmtT(d.target2025)}</td>
                <td>${fmtT(d.realisasi2025)}</td>
                <td style="color:${d.deltaKwh >= 0 ? '#00ff88' : '#f44336'}">${fmtT(d.deltaKwh)}</td>
                <td style="color:${d.deltaKwh >= 0 ? '#00ff88' : '#f44336'}">${fmtPct(d.deltaPct - 1)}</td>
            </tr>`).join("")
        }
    }
}