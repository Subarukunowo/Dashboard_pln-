const COLORS = ["#ff9800","#00ff88","#2196f3","#e91e63","#9c27b0","#00bcd4"]

export default class GolTarifBatamModule {
    constructor(chart) { this.chart = chart }

    #getLabels(filterId) {
        return [...document.querySelectorAll(`#${filterId} option`)]
            .filter(o => o.value !== "all")
            .map(o => o.textContent.trim())
    }

    render(data, state) {
        if (!data) return
        const akum   = state.view === "akumulasi"
        const idx    = parseInt(state.bulan)
        const is2024 = state.tahun === "2024"
        const sliceIdx = (state.bulanRealisasi && state.bulanRealisasi !== "all")
            ? parseInt(state.bulanRealisasi) + 1 : 12

        const labels        = this.#getLabels("filterBulanPie")
        const slicedLabels  = labels.slice(0, sliceIdx)

        // Section 1 — pie
        const nilaiGol = data.golongan.map(g =>
            is2024
                ? (akum ? data.realisasi2024Akum[g][idx] : data.realisasi2024Bul[g][idx])
                : (akum ? data.target2025Akum[g][idx]    : data.target2025Bul[g][idx])
        )
        const judulEl = document.getElementById("judulPie")
        if (judulEl) judulEl.textContent =
            (is2024 ? "Realisasi" : "Target") + " Pendapatan " + state.tahun +
            (akum ? " (Akumulasi)" : " (Bulanan)") + " — " + labels[idx]

        this.chart.pie("chartPieTarif",
            data.golongan.map(g => g.replace(/ \(.\) ?/, '')),
            nilaiGol, COLORS
        )
        this.#renderPieTable(data.golongan, nilaiGol)

        // Section 2 — realisasi (sliced)
        const slicedTarget    = data.realisasiBulanan.slice(0, sliceIdx).map(d => d.target)
        const slicedRealisasi = data.realisasiBulanan.slice(0, sliceIdx).map(d => d.realisasi)
        const slicedDelta     = data.realisasiBulanan.slice(0, sliceIdx).map(d => d.delta)

        this.chart.bar("chartRealisasiBulanan", slicedLabels, [
            { label: "Target",    data: slicedTarget,    backgroundColor: "#ff9800" },
            { label: "Realisasi", data: slicedRealisasi, backgroundColor: "#00ff88" },
        ])
        this.chart.bar("chartDeltaRealisasi", slicedLabels, [
            { label: "Delta", data: slicedDelta, backgroundColor: slicedDelta.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ])

        this.#renderRealisasiTable(data.realisasiBulanan.slice(0, sliceIdx), slicedLabels)
        this.#renderDeltaTable(slicedLabels, slicedDelta)

        // Section 3 — total thn lalu vs thn ini
        const t          = data.total
        const totalLabels = this.#getLabels("filterBulanPie").slice(0, t.length)

        this.chart.bar("chartTotal2024vs2025", totalLabels, [
            { label: "Target Thn Lalu",    data: t.map(d => d.target2024),    backgroundColor: "#555"    },
            { label: "Realisasi Thn Lalu", data: t.map(d => d.realisasi2024), backgroundColor: "#2196f3" },
            { label: "Target Thn Ini",     data: t.map(d => d.target2025),    backgroundColor: "#ff9800" },
            { label: "Realisasi Thn Ini",  data: t.map(d => d.realisasi2025), backgroundColor: "#00ff88" },
        ])
        const deltaTotal   = t.map(d => d.deltaKwh)
        this.chart.bar("chartTotalDelta", totalLabels, [
            { label: "Delta", data: deltaTotal, backgroundColor: deltaTotal.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ])
        const growthTarget = t.map(d => ((d.target2025 / d.target2024) - 1) * 100)
        const growthReal   = t.map(d => ((d.realisasi2025 / d.realisasi2024) - 1) * 100)
        this.chart.line("chartTotalPertumbuhan", totalLabels, [
            { label: "% Target",    data: growthTarget, borderColor: "#ff9800", tension: 0.4, fill: false, pointRadius: 3 },
            { label: "% Realisasi", data: growthReal,   borderColor: "#00ff88", tension: 0.4, fill: false, pointRadius: 3 },
        ])

        this.#renderTotalTable(t, totalLabels)
        this.#renderDeltaTotalTable(totalLabels, deltaTotal)
        this.#renderGrowthTable(totalLabels, growthTarget, growthReal)
    }

    initFilters(state, onChange) {
        document.getElementById("filterTahunPie")?.addEventListener("change",          e => { state.tahun          = e.target.value; onChange() })
        document.getElementById("filterViewPie")?.addEventListener("change",           e => { state.view           = e.target.value; onChange() })
        document.getElementById("filterBulanPie")?.addEventListener("change",          e => { state.bulan          = e.target.value; onChange() })
        document.getElementById("filterBulanRealisasiGol")?.addEventListener("change", e => { state.bulanRealisasi = e.target.value; onChange() })
    }

    #renderPieTable(golongan, nilaiGol) {
        const tbody = document.getElementById("tabelPieTarif")
        if (!tbody) return
        const total = nilaiGol.reduce((a, b) => a + b, 0)
        const fmt   = v => "Rp " + (v / 1000).toFixed(2) + "M"

        tbody.innerHTML = golongan.map((g, i) => {
            const pct = total ? ((nilaiGol[i] / total) * 100).toFixed(1) : "0.0"
            return `<tr>
                <td style="padding:8px 10px; color:#888; font-size:0.72rem; text-align:center;">${g.replace(/ \(.\) ?/, '')}</td>
                <td style="padding:8px 10px; text-align:center; color:var(--accent-green);">${fmt(nilaiGol[i])}</td>
                <td style="padding:8px 10px; text-align:center; color:#aaa;">${pct}%</td>
            </tr>`
        }).join("")
    }

    #renderRealisasiTable(data, labels) {
        const tbody = document.getElementById("tabelRealisasi")
        if (!tbody) return
        const fmt    = v => "Rp " + (v / 1000).toFixed(2) + " Jt"
        const fmtPct = v => (v * 100).toFixed(2) + "%"

        tbody.innerHTML = data.map((d, i) => `<tr>
            <td style="padding:8px 10px; text-align:center;">${labels[i] ?? d.bulan}</td>
            <td style="padding:8px 10px; text-align:center; color:#ff9800;">${fmt(d.target)}</td>
            <td style="padding:8px 10px; text-align:center; color:#00ff88;">${fmt(d.realisasi)}</td>
            <td style="padding:8px 10px; text-align:center; color:${d.delta >= 0 ? '#00ff88' : '#f44336'}">${fmt(d.delta)}</td>
            <td style="padding:8px 10px; text-align:center; color:${d.delta >= 0 ? '#00ff88' : '#f44336'}">${fmtPct(d.pencapaian)}</td>
        </tr>`).join("")
    }

    #renderDeltaTable(labels, delta) {
        const tbody = document.getElementById("tabelDeltaRealisasi")
        if (!tbody) return
        const fmt = v => "Rp " + (v / 1000).toFixed(2) + " Jt"

        tbody.innerHTML = labels.map((bln, i) => `<tr>
            <td style="padding:8px 10px; text-align:center; color:#888;">${bln}</td>
            <td style="padding:8px 10px; text-align:center; color:${delta[i] >= 0 ? '#00ff88' : '#f44336'}">${fmt(delta[i])}</td>
        </tr>`).join("")
    }

    #renderTotalTable(t, labels) {
        const tbody = document.getElementById("tabelTotal")
        if (!tbody) return
        const fmtT   = v => "Rp " + (v / 1_000_000_000).toFixed(2) + "T"
        const fmtPct = v => (v * 100).toFixed(2) + "%"

        tbody.innerHTML = t.map((d, i) => `<tr>
            <td style="padding:8px 10px; text-align:center;">${labels[i] ?? d.bulan}</td>
            <td style="padding:8px 10px; text-align:center; color:#555;">${fmtT(d.target2024)}</td>
            <td style="padding:8px 10px; text-align:center; color:#2196f3;">${fmtT(d.realisasi2024)}</td>
            <td style="padding:8px 10px; text-align:center; color:#ff9800;">${fmtT(d.target2025)}</td>
            <td style="padding:8px 10px; text-align:center; color:#00ff88;">${fmtT(d.realisasi2025)}</td>
            <td style="padding:8px 10px; text-align:center; color:${d.deltaKwh >= 0 ? '#00ff88' : '#f44336'}">${fmtT(d.deltaKwh)}</td>
            <td style="padding:8px 10px; text-align:center; color:${d.deltaKwh >= 0 ? '#00ff88' : '#f44336'}">${fmtPct(d.deltaPct - 1)}</td>
        </tr>`).join("")
    }

    #renderDeltaTotalTable(labels, delta) {
        const tbody = document.getElementById("tabelDeltaTotal")
        if (!tbody) return
        const fmt = v => "Rp " + (v / 1_000_000_000).toFixed(2) + "T"

        tbody.innerHTML = labels.map((bln, i) => `<tr>
            <td style="padding:8px 10px; text-align:center; color:#888;">${bln}</td>
            <td style="padding:8px 10px; text-align:center; color:${delta[i] >= 0 ? '#00ff88' : '#f44336'}">${fmt(delta[i])}</td>
        </tr>`).join("")
    }

    #renderGrowthTable(labels, growthTarget, growthReal) {
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