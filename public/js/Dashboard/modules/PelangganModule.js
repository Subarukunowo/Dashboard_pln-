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

        this.chart.pie("chartPelangganPie", pieLabels, pieData)

        this.chart.make("chartPelangganGol", "bar", pieLabels, [
            { label: "Target 2025",    data: data.golongan.map(g => data.targetGolAkum[g][state.bulan]),    backgroundColor: "#ff9800" },
            { label: "Realisasi 2024", data: data.golongan.map(g => data.realisasiGolAkum[g][state.bulan]), backgroundColor: "#2196f3" },
        ])

        this._populateTarif(data, state.tarif)
        this._renderSideTable(r, rl)
        this._renderPieTable(pieLabels, pieData)
        this._tableRkap(data)
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

    _renderSideTable(r, rl) {
        const tbody = document.getElementById("pelangganSideTable")
        if (!tbody) return
        tbody.innerHTML = LABELS.map((bln, i) => {
            const d     = rl[i] - r[i]
            const color = d >= 0 ? 'var(--accent-green)' : '#f44336'
            return `<tr>
                <td style="color:#888; font-size:0.75rem;">${bln}</td>
                <td style="text-align:right; color:#ff9800;">${(r[i] || 0).toLocaleString('id-ID')}</td>
                <td style="text-align:right; color:var(--accent-green);">${(rl[i] || 0).toLocaleString('id-ID')}</td>
                <td style="text-align:right; color:${color}; font-weight:600;">${d >= 0 ? '+' : ''}${d.toLocaleString('id-ID')}</td>
            </tr>`
        }).join("")
    }

    _renderPieTable(labels, data) {
        const tbody = document.getElementById("pelangganPieTable")
        if (!tbody) return
        const total = data.reduce((a, b) => a + b, 0)
        tbody.innerHTML = labels.map((lbl, i) => {
            const pct = total ? ((data[i] / total) * 100).toFixed(1) : "0.0"
            return `<tr>
                <td style="color:#888; font-size:0.72rem;">${lbl}</td>
                <td style="text-align:right; color:var(--accent-green);">${(data[i] || 0).toLocaleString('id-ID')}</td>
                <td style="text-align:right; color:#aaa;">${pct}%</td>
            </tr>`
        }).join("")
    }

    _tableRkap(data) {
        const tbody = document.getElementById("tabelRkapPelanggan")
        if (tbody && data.rkapData) {
            tbody.innerHTML = data.rkapData.map(d => `<tr>
                <td>${d.golongan}</td>
                <td>${d.targetTw1}</td><td>${d.targetTw2}</td><td>${d.targetTw3}</td><td>${d.targetTw4}</td><td>${d.targetTotal}</td>
                <td>${d.realTw1}</td><td>${d.realTw2}</td><td>${d.realTw3}</td><td>${d.realTw4}</td><td>${d.realTotal}</td>
                <td style="color:${d.pencapaian >= 1 ? '#00ff88' : '#f44336'}">${(d.pencapaian * 100).toFixed(2)}%</td>
            </tr>`).join("")
        }
    }
}