const COLORS = ["#ff9800","#00ff88","#2196f3","#e91e63","#9c27b0","#00bcd4"]
const LABELS = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]

export default class GolTarifBatamModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return
        const akum   = state.view === "akumulasi"
        const idx    = parseInt(state.bulan)
        const is2024 = state.tahun === "2024"
        const sliceIdx = (state.bulanRealisasi && state.bulanRealisasi !== "all")
            ? parseInt(state.bulanRealisasi) + 1 : 12

        // Section 1 — pie
        const nilaiGol = data.golongan.map(g =>
            is2024
                ? (akum ? data.realisasi2024Akum[g][idx] : data.realisasi2024Bul[g][idx])
                : (akum ? data.target2025Akum[g][idx]    : data.target2025Bul[g][idx])
        )
        const judulEl = document.getElementById("judulPie")
        if (judulEl) judulEl.textContent =
            (is2024 ? "Realisasi" : "Target") + " Pendapatan " + state.tahun +
            (akum ? " (Akumulasi)" : " (Bulanan)") + " — " + LABELS[idx]

        this.chart.pie("chartPieTarif",
            data.golongan.map(g => g.replace(/ \(.\) ?/, '')),
            nilaiGol, COLORS
        )
        this.renderPieTable(data.golongan, nilaiGol)

        // Section 2 — realisasi 2025 (sliced)
        const slicedLabels     = LABELS.slice(0, sliceIdx)
        const slicedTarget     = data.realisasiBulanan.slice(0, sliceIdx).map(d => d.target)
        const slicedRealisasi  = data.realisasiBulanan.slice(0, sliceIdx).map(d => d.realisasi)
        const slicedDelta      = data.realisasiBulanan.slice(0, sliceIdx).map(d => d.delta)

        this.chart.bar("chartRealisasiBulanan", slicedLabels, [
            { label: "Target",    data: slicedTarget,    backgroundColor: "#ff9800" },
            { label: "Realisasi", data: slicedRealisasi, backgroundColor: "#00ff88" },
        ])
        this.chart.bar("chartDeltaRealisasi", slicedLabels, [
            { label: "Delta", data: slicedDelta, backgroundColor: slicedDelta.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ])

        // Render tables for Section 2
        this.renderRealisasiTable(data.realisasiBulanan.slice(0, sliceIdx))
        this.renderDeltaTable(slicedLabels, slicedDelta)

        // Section 3 — total 2024 vs 2025
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
        const growthTarget = t.map(d => ((d.target2025/d.target2024)-1)*100)
        const growthReal = t.map(d => ((d.realisasi2025/d.realisasi2024)-1)*100)
        this.chart.line("chartTotalPertumbuhan", t.map(d => d.bulan), [
            { label: "% Target",    data: growthTarget, borderColor: "#ff9800", tension: 0.4, fill: false, pointRadius: 3 },
            { label: "% Realisasi", data: growthReal,   borderColor: "#00ff88", tension: 0.4, fill: false, pointRadius: 3 },
        ])

        // Render tables for Section 3
        this.renderTotalTable(t)
        this.renderDeltaTotalTable(t.map(d => d.bulan), deltaTotal)
        this.renderGrowthTable(t.map(d => d.bulan), growthTarget, growthReal)
    }

    initFilters(state, onChange) {
        document.getElementById("filterTahunPie")?.addEventListener("change", e => { state.tahun = e.target.value; onChange() })
        document.getElementById("filterViewPie")?.addEventListener("change",  e => { state.view  = e.target.value; onChange() })
        document.getElementById("filterBulanPie")?.addEventListener("change", e => { state.bulan = e.target.value; onChange() })
        document.getElementById("filterBulanRealisasiGol")?.addEventListener("change", e => { state.bulanRealisasi = e.target.value; onChange() })
    }

    // Table for Pie Chart
    renderPieTable(golongan, nilaiGol) {
        const tbody = document.getElementById("tabelPieTarif")
        if (!tbody) return
        const total = nilaiGol.reduce((a, b) => a + b, 0)
        const fmt = v => "Rp " + (v/1000).toFixed(2) + "M"
        tbody.innerHTML = golongan.map((g, i) => {
            const pct = total ? ((nilaiGol[i] / total) * 100).toFixed(1) : "0.0"
            return `<tr>
                <td style="padding:8px 10px; color:#888; font-size:0.72rem; text-align:center;">${g.replace(/ \(.\) ?/, '')}</td>
                <td style="padding:8px 10px; text-align:center; color:var(--accent-green);">${fmt(nilaiGol[i])}</td>
                <td style="padding:8px 10px; text-align:center; color:#aaa;">${pct}%</td>
            </tr>`
        }).join("")
    }

    // Table for Realisasi Bulanan
    renderRealisasiTable(data) {
        const tbody = document.getElementById("tabelRealisasi")
        if (!tbody) return
        const fmt    = v => "Rp " + (v/1000).toFixed(2) + " Jt"
        const fmtPct = v => (v * 100).toFixed(2) + "%"
        tbody.innerHTML = data.map(d => `<tr>
            <td style="padding:8px 10px; text-align:center;">${d.bulan}</td>
            <td style="padding:8px 10px; text-align:center; color:#ff9800;">${fmt(d.target)}</td>
            <td style="padding:8px 10px; text-align:center; color:#00ff88;">${fmt(d.realisasi)}</td>
            <td style="padding:8px 10px; text-align:center; color:${d.delta >= 0 ? '#00ff88' : '#f44336'}">${fmt(d.delta)}</td>
            <td style="padding:8px 10px; text-align:center; color:${d.delta >= 0 ? '#00ff88' : '#f44336'}">${fmtPct(d.pencapaian)}</td>
        </tr>`).join("")
    }

    // Table for Delta Realisasi (NEW)
    renderDeltaTable(labels, delta) {
        const tbody = document.getElementById("tabelDeltaRealisasi")
        if (!tbody) return
        const fmt = v => "Rp " + (v/1000).toFixed(2) + " Jt"
        tbody.innerHTML = labels.map((bln, i) => `<tr>
            <td style="padding:8px 10px; text-align:center; color:#888;">${bln}</td>
            <td style="padding:8px 10px; text-align:center; color:${delta[i] >= 0 ? '#00ff88' : '#f44336'}">${fmt(delta[i])}</td>
        </tr>`).join("")
    }

    // Table for Total 2024 vs 2025
    renderTotalTable(t) {
        const tbody = document.getElementById("tabelTotal")
        if (!tbody) return
        const fmtT   = v => "Rp " + (v/1_000_000_000).toFixed(2) + "T"
        const fmtPct = v => (v * 100).toFixed(2) + "%"
        tbody.innerHTML = t.map(d => `<tr>
            <td style="padding:8px 10px; text-align:center;">${d.bulan}</td>
            <td style="padding:8px 10px; text-align:center; color:#555;">${fmtT(d.target2024)}</td>
            <td style="padding:8px 10px; text-align:center; color:#2196f3;">${fmtT(d.realisasi2024)}</td>
            <td style="padding:8px 10px; text-align:center; color:#ff9800;">${fmtT(d.target2025)}</td>
            <td style="padding:8px 10px; text-align:center; color:#00ff88;">${fmtT(d.realisasi2025)}</td>
            <td style="padding:8px 10px; text-align:center; color:${d.deltaKwh >= 0 ? '#00ff88' : '#f44336'}">${fmtT(d.deltaKwh)}</td>
            <td style="padding:8px 10px; text-align:center; color:${d.deltaKwh >= 0 ? '#00ff88' : '#f44336'}">${fmtPct(d.deltaPct - 1)}</td>
        </tr>`).join("")
    }

    // Table for Delta Total (NEW)
    renderDeltaTotalTable(labels, delta) {
        const tbody = document.getElementById("tabelDeltaTotal")
        if (!tbody) return
        const fmt = v => "Rp " + (v/1_000_000_000).toFixed(2) + "T"
        tbody.innerHTML = labels.map((bln, i) => `<tr>
            <td style="padding:8px 10px; text-align:center; color:#888;">${bln}</td>
            <td style="padding:8px 10px; text-align:center; color:${delta[i] >= 0 ? '#00ff88' : '#f44336'}">${fmt(delta[i])}</td>
        </tr>`).join("")
    }

    // Table for Growth Percentage (NEW)
    renderGrowthTable(labels, growthTarget, growthReal) {
        const tbody = document.getElementById("tabelPertumbuhan")
        if (!tbody) return
        const fmt = v => v.toFixed(2) + "%"
        tbody.innerHTML = labels.map((bln, i) => `<tr>
            <td style="padding:8px 10px; text-align:center; color:#888;">${bln}</td>
            <td style="padding:8px 10px; text-align:center; color:#ff9800;">${fmt(growthTarget[i])}</td>
            <td style="padding:8px 10px; text-align:center; color:#00ff88;">${fmt(growthReal[i])}</td>
        </tr>`).join("")
    }
}