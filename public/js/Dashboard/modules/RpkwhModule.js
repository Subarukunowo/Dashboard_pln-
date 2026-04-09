const LABELS = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]
const COLORS = ["#ff9800","#00ff88","#2196f3","#e91e63","#9c27b0","#00bcd4"]

export default class RpkwhModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return
        this._renderRencanaRealisasi(data, state)
        this._renderGolongan(data, state)
        this._renderGrowth(data, state)
        this._renderBppHjr(data, state)
        this._renderAnalisa(data, state)
        this._renderPersen(data)
        this._populateTarif(data, state.tarif)
    }

    initFilters(state, onChange) {
        document.getElementById("filterTarifRpkwh")?.addEventListener("change", e => { state.tarif = e.target.value; onChange() })
        document.getElementById("filterViewRpkwh")?.addEventListener("change",  e => { state.view  = e.target.value; onChange() })
        document.getElementById("filterBulanRpkwh")?.addEventListener("change", e => { state.bulan = e.target.value; onChange() })
        document.getElementById("filterTahunRpkwh")?.addEventListener("change", e => { state.tahun = e.target.value; onChange() })
    }

    _renderRencanaRealisasi(data, state) {
        const akum = state.view === "akumulasi"
        const r  = data.rencanaData[state.tarif]   || new Array(12).fill(0)
        const rl = data.realisasiData[state.tarif] || new Array(12).fill(0)

        // hitung akumulasi kalau dipilih
        const toAkum = arr => arr.map((_, i) => arr.slice(0, i + 1).reduce((a, b) => a + b, 0))
        const displayR  = akum ? toAkum(r)  : r
        const displayRl = akum ? toAkum(rl) : rl

        this.chart.make("chartRencanaRealisasi", "bar", LABELS, [
            { label: "Rencana",   data: displayR,  backgroundColor: "#ff9800" },
            { label: "Realisasi", data: displayRl, backgroundColor: "#00ff88" },
        ], v => v.toFixed(0) + " Rp/kWh")

        const delta = displayRl.map((v, i) => v - displayR[i])
        this.chart.make("chartDeltaRpkwh", "bar", LABELS, [
            { label: "Delta", data: delta, backgroundColor: delta.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ], v => v.toFixed(0) + " Rp/kWh")

        this._renderSideTable(displayR, displayRl)
    }

    _renderGolongan(data, state) {
        const akum   = state.view === "akumulasi"
        const idx    = parseInt(state.bulan)
        const is2024 = state.tahun === "2024"

        const nilaiGol = data.golongan.map(g =>
            is2024
                ? (akum ? data.realisasiGolAkum[g][idx] : data.realisasiGolBul[g][idx])
                : (akum ? data.targetGolAkum[g][idx]    : data.targetGolBul[g][idx])
        )

        const judulEl = document.getElementById("judulPieRpkwh")
        if (judulEl) judulEl.textContent =
            (is2024 ? "Realisasi" : "Target") + " RPKWH " + state.tahun +
            (akum ? " (Akumulasi)" : " (Bulanan)") + " — " + LABELS[idx]

        this.chart.pie("chartPieRpkwh",
            data.golongan.map(g => g.replace(/ \(.\) ?/, '')),
            nilaiGol
        )
        this._renderPieTable(data.golongan.map(g => g.replace(/ \(.\) ?/, '')), nilaiGol)
    }

    _renderGrowth(data, state) {
        const akum = state.view === "akumulasi"
        const datasets = data.golongan.map((g, i) => ({
            label: g.replace(/ \(.\) ?/, ''),
            data: akum ? data.growthAkum[g] : data.growthBul[g],
            borderColor: COLORS[i], tension: 0.4, fill: false, pointRadius: 3
        }))

        this.chart.make("chartGrowthRpkwh", "line", LABELS, datasets,
            v => (v * 100).toFixed(1) + "%"
        )

        // tabel growth
        const tbody = document.getElementById("tabelGrowthRpkwh")
        if (tbody) {
            const fmtPct = v => (v * 100).toFixed(2) + "%"
            tbody.innerHTML = LABELS.map((bln, i) => `<tr>
                <td>${bln}</td>
                ${data.golongan.map(g => {
                    const val = akum ? data.growthAkum[g][i] : data.growthBul[g][i]
                    return `<td style="color:${val >= 0 ? '#00ff88' : '#f44336'}">${fmtPct(val)}</td>`
                }).join("")}
            </tr>`).join("")
        }
    }

    _renderBppHjr(data, state) {
        const tarifBpp = Object.keys(data.bppHjrData)

        this.chart.make("chartBppHjr", "line", LABELS,
            tarifBpp.map((t, i) => ({
                label: t, data: data.bppHjrData[t],
                borderColor: COLORS[i % COLORS.length], tension: 0.4, fill: false, pointRadius: 3
            })),
            v => v.toFixed(0) + " Rp/kWh"
        )

        // tabel bppHjr
        const tbody = document.getElementById("tabelBppHjr")
        if (tbody) {
            tbody.innerHTML = LABELS.map((bln, i) => `<tr>
                <td>${bln}</td>
                ${tarifBpp.map(t => `<td>${(data.bppHjrData[t][i] || 0).toFixed(0)}</td>`).join("")}
            </tr>`).join("")

            // update thead dinamis
            const thead = document.getElementById("theadBppHjr")
            if (thead) {
                thead.innerHTML = `<tr>
                    <th>Bulan</th>
                    ${tarifBpp.map(t => `<th style="font-size:0.65rem;">${t}</th>`).join("")}
                </tr>`
            }
        }
    }

    _renderAnalisa(data, state) {
        const akum = state.view === "akumulasi"
        const a = data.analisaData

        this.chart.make("chartAnalisa2024vs2025", "bar", a.map(d => d.bulan), [
            { label: "Target 2024",    data: a.map(d => akum ? d.akum2024          : d.target2024),    backgroundColor: "#555" },
            { label: "Realisasi 2024", data: a.map(d => akum ? d.realisasiAkum2024 : d.realisasi2024), backgroundColor: "#2196f3" },
            { label: "Target 2025",    data: a.map(d => akum ? d.akum2025          : d.target2025),    backgroundColor: "#ff9800" },
            { label: "Realisasi 2025", data: a.map(d => akum ? d.realisasiAkum2025 : d.realisasi2025), backgroundColor: "#00ff88" },
        ], v => v.toFixed(0) + " Rp/kWh")

        const delta = a.map(d => akum ? d.deltaAkum : d.deltaRpkwh)
        this.chart.make("chartAnalisaDelta", "bar", a.map(d => d.bulan), [
            { label: "Delta", data: delta, backgroundColor: delta.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ], v => v.toFixed(0) + " Rp/kWh")

        this._renderAnalisaSideTable(a, akum)

        const tbody = document.getElementById("tabelAnalisa")
        if (tbody) {
            const fmtPct = v => (v * 100).toFixed(2) + "%"
            tbody.innerHTML = a.map(d => {
                const dlt = akum ? d.deltaAkum : d.deltaRpkwh
                const dPct = akum ? d.deltaAkumPct : d.deltaPct
                return `<tr>
                    <td>${d.bulan}</td>
                    <td>${(akum ? d.akum2024          : d.target2024).toFixed(2)}</td>
                    <td>${(akum ? d.realisasiAkum2024 : d.realisasi2024).toFixed(2)}</td>
                    <td>${(akum ? d.akum2025          : d.target2025).toFixed(2)}</td>
                    <td>${(akum ? d.realisasiAkum2025 : d.realisasi2025).toFixed(2)}</td>
                    <td style="color:${dlt >= 0 ? '#00ff88' : '#f44336'}">${dlt.toFixed(2)}</td>
                    <td style="color:${dlt >= 0 ? '#00ff88' : '#f44336'}">${fmtPct(dPct - 1)}</td>
                    <td>${fmtPct(d.pencapaianTarget)}</td>
                    <td>${fmtPct(d.pencapaianRealisasi)}</td>
                </tr>`
            }).join("")
        }
    }

    _renderPersen(data) {
        const p = data.persenData
        this.chart.make("chartPersenRpkwh", "bar", p.map(d => d.bulan), [
            { label: "Target",    data: p.map(d => d.target),    backgroundColor: "#ff9800" },
            { label: "Realisasi", data: p.map(d => d.realisasi), backgroundColor: "#00ff88" },
        ], v => v.toFixed(0) + " Rp/kWh")

        const tbody = document.getElementById("tabelPersen")
        if (tbody) {
            tbody.innerHTML = p.map(d => `<tr>
                <td>${d.bulan}</td>
                <td>${d.target.toFixed(2)}</td>
                <td>${d.realisasi.toFixed(2)}</td>
                <td style="color:${d.delta >= 0 ? '#00ff88' : '#f44336'}">${d.delta.toFixed(2)}</td>
            </tr>`).join("")
        }
    }

    _renderSideTable(r, rl) {
        const tbody = document.getElementById("rpkwhSideTable")
        if (!tbody) return
        tbody.innerHTML = LABELS.map((bln, i) => {
            const d     = (rl[i] || 0) - (r[i] || 0)
            const color = d >= 0 ? 'var(--accent-green)' : '#f44336'
            return `<tr>
                <td style="color:#888; font-size:0.75rem;">${bln}</td>
                <td style="text-align:right; color:#ff9800;">${(r[i] || 0).toFixed(0)}</td>
                <td style="text-align:right; color:var(--accent-green);">${(rl[i] || 0).toFixed(0)}</td>
                <td style="text-align:right; color:${color}; font-weight:600;">${d >= 0 ? '+' : ''}${d.toFixed(0)}</td>
            </tr>`
        }).join("")
    }

    _renderPieTable(labels, data) {
        const tbody = document.getElementById("rpkwhPieTable")
        if (!tbody) return
        const total = data.reduce((a, b) => a + b, 0)
        tbody.innerHTML = labels.map((lbl, i) => {
            const pct = total ? ((data[i] / total) * 100).toFixed(1) : "0.0"
            return `<tr>
                <td style="color:#888; font-size:0.72rem;">${lbl}</td>
                <td style="text-align:right; color:var(--accent-green);">${(data[i] || 0).toFixed(0)}</td>
                <td style="text-align:right; color:#aaa;">${pct}%</td>
            </tr>`
        }).join("")
    }

    _renderAnalisaSideTable(a, akum) {
        const tbody = document.getElementById("rpkwhAnalisaSideTable")
        if (!tbody) return
        tbody.innerHTML = a.map(d => {
            const delta = akum ? d.deltaAkum : d.deltaRpkwh
            const color = delta >= 0 ? 'var(--accent-green)' : '#f44336'
            return `<tr>
                <td style="color:#888; font-size:0.75rem;">${d.bulan}</td>
                <td style="text-align:right; color:#555;">${(akum ? d.akum2024          : d.target2024).toFixed(0)}</td>
                <td style="text-align:right; color:#2196f3;">${(akum ? d.realisasiAkum2024 : d.realisasi2024).toFixed(0)}</td>
                <td style="text-align:right; color:#ff9800;">${(akum ? d.akum2025          : d.target2025).toFixed(0)}</td>
                <td style="text-align:right; color:var(--accent-green);">${(akum ? d.realisasiAkum2025 : d.realisasi2025).toFixed(0)}</td>
                <td style="text-align:right; color:${color}; font-weight:600;">${delta >= 0 ? '+' : ''}${delta.toFixed(0)}</td>
            </tr>`
        }).join("")
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
}