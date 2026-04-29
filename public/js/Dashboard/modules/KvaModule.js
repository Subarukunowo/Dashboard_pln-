const LABELS = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]

export default class KvaModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return

        const r  = data.rencanaData[state.tarif]   || new Array(12).fill(0)
        const rl = data.realisasiData[state.tarif] || new Array(12).fill(0)
        const akum = state.view === "akumulasi"

        const toAkum = arr => arr.map((_, i) => arr.slice(0, i + 1).reduce((a, b) => a + b, 0))
        const displayR  = akum ? toAkum(r)  : r
        const displayRl = akum ? toAkum(rl) : rl

        const sliceIdx     = (state.bulan ?? 11) + 1
        const slicedLabels = LABELS.slice(0, sliceIdx)
        const slicedR      = displayR.slice(0, sliceIdx)
        const slicedRl     = displayRl.slice(0, sliceIdx)

        this.chart.make("chartKvaBar", "bar", slicedLabels, [
            { label: "Rencana",   data: slicedR,  backgroundColor: "#ff9800" },
            { label: "Realisasi", data: slicedRl, backgroundColor: "#00ff88" },
        ])

        const delta = slicedRl.map((v, i) => v - slicedR[i])
        this.chart.make("chartKvaDelta", "bar", slicedLabels, [
            { label: "Delta", data: delta, backgroundColor: delta.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ])

        const pieLabels = data.golongan.map(g => g.replace(/ \(.\) ?/, ''))
        const pieData   = data.golongan.map(g => data.targetGolAkum[g][state.bulan ?? 11] || 0)
        const pieTarget = data.golongan.map(g => data.targetGolAkum[g][state.bulan ?? 11])
        const pieReal24 = data.golongan.map(g => data.realisasiGolAkum[g][state.bulan ?? 11])

        this.chart.pie("chartKvaPie", pieLabels, pieData)
        this.chart.make("chartKvaGol", "bar", pieLabels, [
            { label: "Target 2025",    data: pieTarget, backgroundColor: "#ff9800" },
            { label: "Realisasi 2024", data: pieReal24, backgroundColor: "#2196f3" },
        ])

        const p = data.perbandinganData
        this.chart.make("chartKvaPerbandingan", "bar", p.map(d => d.bulan), [
            { label: "Target 2024",    data: p.map(d => akum ? d.akum2024          : d.target2024),    backgroundColor: "#555" },
            { label: "Realisasi 2024", data: p.map(d => akum ? d.realisasiAkum2024 : d.realisasi2024), backgroundColor: "#2196f3" },
            { label: "Target 2025",    data: p.map(d => akum ? d.akum2025          : d.target2025),    backgroundColor: "#ff9800" },
            { label: "Realisasi 2025", data: p.map(d => akum ? d.realisasiAkum2025 : d.realisasi2025), backgroundColor: "#00ff88" },
        ])

        const deltaPerb = p.map(d => akum ? d.deltaAkumKva : d.deltaKva)
        this.chart.make("chartKvaDeltaPerbandingan", "bar", p.map(d => d.bulan), [
            { label: "Delta", data: deltaPerb, backgroundColor: deltaPerb.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ])

        this._populateTarif(data, state.tarif)

        // Render all tables
        this.renderBarTable(slicedLabels, slicedR, slicedRl)
        this.renderDeltaTable(slicedLabels, delta)
        this.renderPieTable(pieLabels, pieData)
        this.renderGolTable(pieLabels, pieTarget, pieReal24)
        this.renderPerbandinganTable(p, akum)
        this.renderDeltaPerbandinganTable(p.map(d => d.bulan), deltaPerb)
        this.renderRkapTable(data)
        this.renderPerbandinganDetailTable(data, akum)
    }

    initFilters(state, onChange) {
        document.getElementById("filterTarifKva")?.addEventListener("change",  e => { state.tarif = e.target.value;       onChange() })
        document.getElementById("filterBulanKva")?.addEventListener("change",  e => { state.bulan = parseInt(e.target.value); onChange() })
        document.getElementById("filterViewKva")?.addEventListener("change",   e => { state.view  = e.target.value;       onChange() })
    }

    _populateTarif(data, currentTarif) {
        const sel = document.getElementById("filterTarifKva")
        if (sel && sel.options.length === 0 && data.tarif) {
            data.tarif.forEach(t => {
                const opt = document.createElement("option")
                opt.value = t; opt.textContent = t
                sel.appendChild(opt)
            })
            sel.value = currentTarif
        }
    }

    // Table for Bar Chart (Rencana vs Realisasi)
    renderBarTable(labels, r, rl) {
        const tbody = document.getElementById("kvaSideTable")
        if (!tbody) return
        const fmt = v => (v || 0).toLocaleString('id-ID')
        tbody.innerHTML = labels.map((bln, i) => {
            const d     = (rl[i] || 0) - (r[i] || 0)
            const color = d >= 0 ? 'var(--accent-green)' : '#f44336'
            return `<tr>
                <td style="padding:8px 10px; color:#888; font-size:0.75rem; text-align:center;">${bln}</td>
                <td style="padding:8px 10px; text-align:center; color:#ff9800;">${fmt(r[i])}</td>
                <td style="padding:8px 10px; text-align:center; color:var(--accent-green);">${fmt(rl[i])}</td>
                <td style="padding:8px 10px; text-align:center; color:${color}; font-weight:600;">${d >= 0 ? '+' : ''}${fmt(d)}</td>
            </tr>`
        }).join("")
    }

    // Table for Delta Chart (NEW)
    renderDeltaTable(labels, delta) {
        const tbody = document.getElementById("tabelKvaDelta")
        if (!tbody) return
        const fmt = v => (v || 0).toLocaleString('id-ID')
        tbody.innerHTML = labels.map((bln, i) => `<tr>
            <td style="padding:8px 10px; color:#888; font-size:0.75rem; text-align:center;">${bln}</td>
            <td style="padding:8px 10px; text-align:center; color:${delta[i] >= 0 ? '#00ff88' : '#f44336'}; font-weight:600;">
                ${delta[i] >= 0 ? '+' : ''}${fmt(delta[i])}
            </td>
        </tr>`).join("")
    }

    // Table for Pie Chart
    renderPieTable(labels, data) {
        const tbody = document.getElementById("kvaPieTable")
        if (!tbody) return
        const total = data.reduce((a, b) => a + b, 0)
        tbody.innerHTML = labels.map((lbl, i) => {
            const pct = total ? ((data[i] / total) * 100).toFixed(1) : "0.0"
            return `<tr>
                <td style="padding:8px 10px; color:#888; font-size:0.72rem; text-align:center;">${lbl}</td>
                <td style="padding:8px 10px; text-align:center; color:var(--accent-green);">${(data[i] || 0).toLocaleString('id-ID')}</td>
                <td style="padding:8px 10px; text-align:center; color:#aaa;">${pct}%</td>
            </tr>`
        }).join("")
    }

    // Table for Golongan Bar Chart (NEW)
    renderGolTable(labels, target, real24) {
        const tbody = document.getElementById("tabelKvaGol")
        if (!tbody) return
        const fmt = v => (v || 0).toLocaleString('id-ID')
        tbody.innerHTML = labels.map((lbl, i) => `<tr>
            <td style="padding:8px 10px; color:#888; font-size:0.72rem; text-align:center;">${lbl}</td>
            <td style="padding:8px 10px; text-align:center; color:#ff9800;">${fmt(target[i])}</td>
            <td style="padding:8px 10px; text-align:center; color:#2196f3;">${fmt(real24[i])}</td>
        </tr>`).join("")
    }

    // Table for Perbandingan Chart
    renderPerbandinganTable(p, akum) {
        const tbody = document.getElementById("kvaPerbandinganSideTable")
        if (!tbody) return
        const fmt = v => (v / 1000).toFixed(1)
        tbody.innerHTML = p.map(d => {
            const delta = akum ? d.deltaAkumKva : d.deltaKva
            const color = delta >= 0 ? 'var(--accent-green)' : '#f44336'
            return `<tr>
                <td style="padding:8px 10px; color:#888; font-size:0.75rem; text-align:center;">${d.bulan}</td>
                <td style="padding:8px 10px; text-align:center; color:#555;">${fmt(akum ? d.akum2024          : d.target2024)}</td>
                <td style="padding:8px 10px; text-align:center; color:#2196f3;">${fmt(akum ? d.realisasiAkum2024 : d.realisasi2024)}</td>
                <td style="padding:8px 10px; text-align:center; color:#ff9800;">${fmt(akum ? d.akum2025          : d.target2025)}</td>
                <td style="padding:8px 10px; text-align:center; color:var(--accent-green);">${fmt(akum ? d.realisasiAkum2025 : d.realisasi2025)}</td>
                <td style="padding:8px 10px; text-align:center; color:${color}; font-weight:600;">${delta >= 0 ? '+' : ''}${fmt(delta)}</td>
            </tr>`
        }).join("")
    }

    // Table for Delta Perbandingan Chart (NEW)
    renderDeltaPerbandinganTable(labels, delta) {
        const tbody = document.getElementById("tabelKvaDeltaPerbandingan")
        if (!tbody) return
        const fmt = v => (v / 1000).toFixed(1)
        tbody.innerHTML = labels.map((bln, i) => `<tr>
            <td style="padding:8px 10px; color:#888; font-size:0.75rem; text-align:center;">${bln}</td>
            <td style="padding:8px 10px; text-align:center; color:${delta[i] >= 0 ? '#00ff88' : '#f44336'}; font-weight:600;">
                ${delta[i] >= 0 ? '+' : ''}${fmt(delta[i])}
            </td>
        </tr>`).join("")
    }

    // Table for RKAP
    renderRkapTable(data) {
        const tbody = document.getElementById("tabelRkapKva")
        if (!tbody || !data.rkapData) return
        tbody.innerHTML = data.rkapData.map(d => `<tr>
            <td style="padding:8px 10px; text-align:center;">${d.golongan}</td>
            <td style="padding:8px 10px; text-align:center;">${d.targetTw1.toFixed(2)}</td>
            <td style="padding:8px 10px; text-align:center;">${d.targetTw2.toFixed(2)}</td>
            <td style="padding:8px 10px; text-align:center;">${d.targetTw3.toFixed(2)}</td>
            <td style="padding:8px 10px; text-align:center;">${d.targetTw4.toFixed(2)}</td>
            <td style="padding:8px 10px; text-align:center;">${d.targetTotal.toFixed(2)}</td>
            <td style="padding:8px 10px; text-align:center;">${d.realTw1.toFixed(2)}</td>
            <td style="padding:8px 10px; text-align:center;">${d.realTw2.toFixed(2)}</td>
            <td style="padding:8px 10px; text-align:center;">${d.realTw3.toFixed(2)}</td>
            <td style="padding:8px 10px; text-align:center;">${d.realTw4.toFixed(2)}</td>
            <td style="padding:8px 10px; text-align:center;">${d.realTotal.toFixed(2)}</td>
            <td style="padding:8px 10px; text-align:center; color:${d.pencapaian >= 1 ? '#00ff88' : '#f44336'}">
                ${(d.pencapaian * 100).toFixed(2)}%
            </td>
        </tr>`).join("")
    }

    // Table for Perbandingan Detail
    renderPerbandinganDetailTable(data, akum) {
        const tbody = document.getElementById("tabelPerbandinganKva")
        if (!tbody || !data.perbandinganData) return
        const fmt    = v => (v / 1000).toFixed(2) + " kVA"
        const fmtPct = v => (v * 100).toFixed(2) + "%"
        tbody.innerHTML = data.perbandinganData.map(d => `<tr>
            <td style="padding:8px 10px; text-align:center;">${d.bulan}</td>
            <td style="padding:8px 10px; text-align:center; color:#555;">${fmt(akum ? d.akum2024          : d.target2024)}</td>
            <td style="padding:8px 10px; text-align:center; color:#2196f3;">${fmt(akum ? d.realisasiAkum2024 : d.realisasi2024)}</td>
            <td style="padding:8px 10px; text-align:center; color:#ff9800;">${fmt(akum ? d.akum2025          : d.target2025)}</td>
            <td style="padding:8px 10px; text-align:center; color:var(--accent-green);">${fmt(akum ? d.realisasiAkum2025 : d.realisasi2025)}</td>
            <td style="padding:8px 10px; text-align:center; color:${d.deltaKva >= 0 ? '#00ff88' : '#f44336'}">
                ${fmt(akum ? d.deltaAkumKva : d.deltaKva)}
            </td>
            <td style="padding:8px 10px; text-align:center; color:${d.deltaKva >= 0 ? '#00ff88' : '#f44336'}">
                ${fmtPct(akum ? d.deltaAkumPct : d.deltaPct)}
            </td>
            <td style="padding:8px 10px; text-align:center;">${fmtPct(d.pencapaianTarget)}</td>
            <td style="padding:8px 10px; text-align:center;">${fmtPct(d.pencapaianRealisasi)}</td>
        </tr>`).join("")
    }
}