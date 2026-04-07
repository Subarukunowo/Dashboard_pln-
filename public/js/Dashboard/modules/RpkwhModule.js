const COLORS = ["#ff9800","#00ff88","#2196f3","#e91e63","#9c27b0","#00bcd4"]
const LABELS = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]

export default class RpkwhModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return
        const akum = state.view === "akumulasi"
        const idx = parseInt(state.bulan)
        const is2024 = state.tahun === "2024"

        const r  = data.rencanaData[state.tarif]   || new Array(12).fill(0)
        const rl = data.realisasiData[state.tarif] || new Array(12).fill(0)

        this.chart.make("chartRencanaRealisasi", "bar", LABELS, [
            { label: "Rencana",   data: r,  backgroundColor: "#ff9800" },
            { label: "Realisasi", data: rl, backgroundColor: "#00ff88" },
        ], v => v.toFixed(0) + " Rp/kWh")

        const delta = rl.map((v, i) => v - r[i])
        this.chart.make("chartDeltaRpkwh", "bar", LABELS, [
            { label: "Delta", data: delta, backgroundColor: delta.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ])

        const nilaiGol = data.golongan.map(g =>
            is2024
                ? (akum ? data.realisasiGolAkum[g][idx] : data.realisasiGolBul[g][idx])
                : (akum ? data.targetGolAkum[g][idx]    : data.targetGolBul[g][idx])
        )
        const judulEl = document.getElementById("judulPieRpkwh")
        if (judulEl) judulEl.textContent =
            (is2024 ? "Realisasi" : "Target") + " RPKWH " + state.tahun +
            (akum ? " (Akumulasi)" : " (Bulanan)") + " — " + LABELS[idx]
        this.chart.pie("chartPieRpkwh", data.golongan.map(g => g.replace(/ \(.\) ?/, '')), nilaiGol)

        this.chart.make("chartGrowthRpkwh", "line", LABELS,
            data.golongan.map((g, i) => ({
                label: g.replace(/ \(.\) ?/, ''),
                data: akum ? data.growthAkum[g] : data.growthBul[g],
                borderColor: COLORS[i], tension: 0.4, fill: false, pointRadius: 3
            })), v => (v * 100).toFixed(1) + "%"
        )

        const tarifBpp = Object.keys(data.bppHjrData)
        this.chart.make("chartBppHjr", "line", LABELS,
            tarifBpp.map((t, i) => ({
                label: t, data: data.bppHjrData[t],
                borderColor: COLORS[i % COLORS.length], tension: 0.4, fill: false, pointRadius: 3
            })), v => v.toFixed(0) + " Rp/kWh"
        )

        const a = data.analisaData
        this.chart.make("chartAnalisa2024vs2025", "bar", a.map(d => d.bulan), [
            { label: "Target 2024",    data: a.map(d => d.target2024),    backgroundColor: "#555" },
            { label: "Realisasi 2024", data: a.map(d => d.realisasi2024), backgroundColor: "#2196f3" },
            { label: "Target 2025",    data: a.map(d => d.target2025),    backgroundColor: "#ff9800" },
            { label: "Realisasi 2025", data: a.map(d => d.realisasi2025), backgroundColor: "#00ff88" },
        ], v => v.toFixed(0) + " Rp/kWh")

        const deltaA = a.map(d => d.deltaRpkwh)
        this.chart.make("chartAnalisaDelta", "bar", a.map(d => d.bulan), [
            { label: "Delta", data: deltaA, backgroundColor: deltaA.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ])

        const p = data.persenData
        this.chart.make("chartPersenRpkwh", "bar", p.map(d => d.bulan), [
            { label: "Target",    data: p.map(d => d.target),    backgroundColor: "#ff9800" },
            { label: "Realisasi", data: p.map(d => d.realisasi), backgroundColor: "#00ff88" },
        ], v => v.toFixed(0) + " Rp/kWh")

        this._populateTarif(data, state.tarif)
        this._tables(a, p)
    }

    initFilters(state, onChange) {
        document.getElementById("filterTarifRpkwh")?.addEventListener("change", e => { state.tarif = e.target.value; onChange() })
        document.getElementById("filterViewRpkwh")?.addEventListener("change",  e => { state.view  = e.target.value; onChange() })
        document.getElementById("filterBulanRpkwh")?.addEventListener("change", e => { state.bulan = e.target.value; onChange() })
        document.getElementById("filterTahunRpkwh")?.addEventListener("change", e => { state.tahun = e.target.value; onChange() })
    }

    _populateTarif(data, currentTarif) {
        const sel = document.getElementById("filterTarifRpkwh")
        if (sel && sel.options.length === 0 && data.tarif) {
            data.tarif.forEach(t => {
                const opt = document.createElement("option")
                opt.value = t; opt.textContent = t
                sel.appendChild(opt)
            })
            sel.value = currentTarif
        }
    }

    _tables(a, p) {
        const tbodyA = document.getElementById("tabelAnalisa")
        if (tbodyA) {
            const fmtPct = v => (v * 100).toFixed(2) + "%"
            tbodyA.innerHTML = a.map(d => `<tr>
                <td>${d.bulan}</td>
                <td>${d.target2024.toFixed(2)}</td>
                <td>${d.realisasi2024.toFixed(2)}</td>
                <td>${d.target2025.toFixed(2)}</td>
                <td>${d.realisasi2025.toFixed(2)}</td>
                <td style="color:${d.deltaRpkwh >= 0 ? '#00ff88' : '#f44336'}">${d.deltaRpkwh.toFixed(2)}</td>
                <td style="color:${d.deltaRpkwh >= 0 ? '#00ff88' : '#f44336'}">${fmtPct(d.deltaPct - 1)}</td>
                <td>${fmtPct(d.pencapaianTarget)}</td>
                <td>${fmtPct(d.pencapaianRealisasi)}</td>
            </tr>`).join("")
        }
        const tbodyP = document.getElementById("tabelPersen")
        if (tbodyP) {
            tbodyP.innerHTML = p.map(d => `<tr>
                <td>${d.bulan}</td>
                <td>${d.target.toFixed(2)}</td>
                <td>${d.realisasi.toFixed(2)}</td>
                <td style="color:${d.delta >= 0 ? '#00ff88' : '#f44336'}">${d.delta.toFixed(2)}</td>
            </tr>`).join("")
        }
    }
}