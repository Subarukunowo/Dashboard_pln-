const COLORS = ["#ff9800","#00ff88","#2196f3","#e91e63","#9c27b0","#00bcd4"]
const LABELS = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]

export default class PelangganModule {
    constructor(chart) { this.chart = chart }

    render(data, state) {
        if (!data) return
        const r  = data.rencanaData[state.tarif]   || new Array(12).fill(0)
        const rl = data.realisasiData[state.tarif] || new Array(12).fill(0)

        
        this.chart.make("chartPelangganBar", "bar", LABELS, [
            { label: "Rencana",   data: r,  backgroundColor: "#ff9800" },
            { label: "Realisasi", data: rl, backgroundColor: "#00ff88" },
        ])

        const delta = rl.map((v, i) => v - r[i])
        this.chart.make("chartPelangganDelta", "bar", LABELS, [
            { label: "Delta", data: delta, backgroundColor: delta.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ])

        const pieLabels = data.golongan.map(g => g.replace(/ \(.\) ?/, ''))
        const pieData   = data.golongan.map(g => data.targetGolAkum[g][state.bulan] || 0)
        const pieTarget = data.golongan.map(g => data.targetGolAkum[g][state.bulan])
        const pieReal24 = data.golongan.map(g => data.realisasiGolAkum[g][state.bulan])

        this.chart.pie("chartPelangganPie", pieLabels, pieData)
        this.chart.make("chartPelangganGol", "bar", pieLabels, [
            { label: "Target 2025",    data: pieTarget, backgroundColor: "#ff9800" },
            { label: "Realisasi 2024", data: pieReal24, backgroundColor: "#2196f3" },
        ])

        this._populateTarif(data, state.tarif)

        // Render all tables
        this.renderBarTable(r, rl)
        this.renderDeltaTable(LABELS, delta)
        this.renderPieTable(pieLabels, pieData)
        this.renderGolTable(pieLabels, pieTarget, pieReal24)
        this.renderRkapTable(data)
    }

    initFilters(state, onChange) {
        document.getElementById("filterTarifPelanggan")?.addEventListener("change", e => {
            state.tarif = e.target.value; onChange()
        })
        document.getElementById("filterBulanPelanggan")?.addEventListener("change", e => {
            state.bulan = parseInt(e.target.value); onChange()
        })
    }

    _populateTarif(data, currentTarif) {
        const sel = document.getElementById("filterTarifPelanggan")
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
    renderBarTable(r, rl) {
        const tbody = document.getElementById("pelangganSideTable")
        if (!tbody) return
        tbody.innerHTML = LABELS.map((bln, i) => {
            const d     = (rl[i] || 0) - (r[i] || 0)
            const color = d >= 0 ? 'var(--accent-green)' : '#f44336'
            return `<tr>
                <td style="padding:8px 10px; color:#888; font-size:0.75rem; text-align:center;">${bln}</td>
                <td style="padding:8px 10px; text-align:center; color:var(--accent-orange);">${(r[i] || 0).toLocaleString('id-ID')}</td>
                <td style="padding:8px 10px; text-align:center; color:var(--accent-green);">${(rl[i] || 0).toLocaleString('id-ID')}</td>
                <td style="padding:8px 10px; text-align:center; color:${color}; font-weight:600;">${d >= 0 ? '+' : ''}${(d || 0).toLocaleString('id-ID')}</td>
            </tr>`
        }).join("")
    }

    // Table for Delta Chart
    renderDeltaTable(labels, delta) {
        const tbody = document.getElementById("tabelPelangganDelta")
        if (!tbody) return
        tbody.innerHTML = labels.map((bln, i) => {
            const d = delta[i] || 0
            const color = d >= 0 ? '#00ff88' : '#f44336'
            return `<tr>
                <td style="padding:8px 10px; color:#888; font-size:0.75rem; text-align:center;">${bln}</td>
                <td style="padding:8px 10px; text-align:center; color:${color}; font-weight:600;">${d >= 0 ? '+' : ''}${d.toLocaleString('id-ID')}</td>
            </tr>`
        }).join("")
    }

    // Table for Pie Chart
    renderPieTable(labels, data) {
        const tbody = document.getElementById("pelangganPieTable")
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

    // Table for Golongan Bar Chart
    renderGolTable(labels, target, real24) {
        const tbody = document.getElementById("tabelPelangganGol")
        if (!tbody) return
        tbody.innerHTML = labels.map((lbl, i) => `<tr>
            <td style="padding:8px 10px; color:#888; font-size:0.72rem; text-align:center;">${lbl}</td>
            <td style="padding:8px 10px; text-align:center; color:var(--accent-orange);">${(target[i] || 0).toLocaleString('id-ID')}</td>
            <td style="padding:8px 10px; text-align:center; color:#2196f3;">${(real24[i] || 0).toLocaleString('id-ID')}</td>
        </tr>`).join("")
    }

    // Table for RKAP
    renderRkapTable(data) {
        const tbody = document.getElementById("tabelRkapPelanggan")
        if (!tbody || !data.rkapData) return
        tbody.innerHTML = data.rkapData.map(d => `<tr>
            <td style="padding:8px 10px; text-align:center;">${d.golongan}</td>
            <td style="padding:8px 10px; text-align:center;">${d.targetTw1}</td>
            <td style="padding:8px 10px; text-align:center;">${d.targetTw2}</td>
            <td style="padding:8px 10px; text-align:center;">${d.targetTw3}</td>
            <td style="padding:8px 10px; text-align:center;">${d.targetTw4}</td>
            <td style="padding:8px 10px; text-align:center; border-right:1px solid #444;">${d.targetTotal}</td>
            <td style="padding:8px 10px; text-align:center; border-left:1px solid #444;">${d.realTw1}</td>
            <td style="padding:8px 10px; text-align:center;">${d.realTw2}</td>
            <td style="padding:8px 10px; text-align:center;">${d.realTw3}</td>
            <td style="padding:8px 10px; text-align:center;">${d.realTw4}</td>
            <td style="padding:8px 10px; text-align:center;">${d.realTotal}</td>
            <td style="padding:8px 10px; text-align:center; color:${d.pencapaian >= 1 ? '#00ff88' : '#f44336'}; font-weight:600;">
                ${(d.pencapaian * 100).toFixed(2)}%
            </td>
        </tr>`).join("")
    }
}