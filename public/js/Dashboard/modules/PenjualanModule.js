import PenjualanService from "../services/PenjualanService.js"
import PenjualanChart from "../charts/PenjualanChart.js"

export default class PenjualanModule {
    constructor(sheetsService, labels) {
        this.service = new PenjualanService(sheetsService)
        this.chart = new PenjualanChart("penjualanChart")
        this.data = null
        this.labels = labels
    }

    async load() {
        this.data = await this.service.getKinerjaData()
    }

    render(state) {
        if (!this.data) return
        const { view, tarif, month } = state
        let target, realisasi, labels

        if (tarif === "JUMLAH") {
            const d = this.data[view]
            if (!d) return
            const isPercent = view.includes('persen')
            target    = isPercent ? [] : (d.target || [])
            realisasi = isPercent ? (d.realisasi || d) : (d.realisasi || [])
            labels    = [...this.labels]
        } else {
            const d = this.data.perTarif?.[tarif]
            if (!d) return
            target    = d.target
            realisasi = d.realisasi
            labels    = [...this.labels]
        }

        // Handle single month filter
        if (month !== "all") {
            const idx = parseInt(month)
            const slicedTarget    = target.length ? [target[idx]] : []
            const slicedRealisasi = [realisasi[idx]]
            const slicedLabels    = [this.labels[idx]]
            
            this.chart.render(slicedTarget, slicedRealisasi, slicedLabels, view)
            this.renderTable(slicedLabels, slicedTarget, slicedRealisasi)
        } else {
            this.chart.render(target, realisasi, labels, view)
            this.renderTable(labels, target, realisasi)
        }
    }

    initFilters(state, onChange) {
        document.getElementById("filterViewPenjualan")?.addEventListener("change", e => {
            state.view = e.target.value; onChange()
        })
        document.getElementById("filterTarif")?.addEventListener("change", e => {
            state.tarif = e.target.value; onChange()
        })
        document.getElementById("filterBulanPenjualan")?.addEventListener("change", e => {
            state.month = e.target.value; onChange()
        })
    }

    // Render table for Penjualan Chart (centered)
    renderTable(labels, target, realisasi) {
        const tbody = document.getElementById("penjualanTableBody")
        if (!tbody) return
        
        // Determine format based on view type (persen vs numeric)
        const isPercent = labels.length > 0 && 
            (typeof realisasi[0] === 'number' && realisasi[0] <= 100 && realisasi[0] >= 0)
        
        const fmt = v => {
            if (v === null || v === undefined) return '—'
            if (isPercent) return (v * 100).toFixed(2) + '%'
            return (v / 1_000_000).toFixed(2) + ' jt'
        }
        
        tbody.innerHTML = labels.map((bln, i) => {
            const t = target[i] ?? 0
            const r = realisasi[i] ?? 0
            const d = r - t
            const color = d >= 0 ? 'var(--accent-green)' : '#f44336'
            
            return `<tr>
                <td style="padding:8px 10px; color:#888; font-size:0.75rem; text-align:center;">${bln}</td>
                <td style="padding:8px 10px; text-align:center; color:var(--accent-orange);">${fmt(t)}</td>
                <td style="padding:8px 10px; text-align:center; color:var(--accent-green);">${fmt(r)}</td>
                <td style="padding:8px 10px; text-align:center; color:${color}; font-weight:600;">
                    ${d !== 0 ? (d >= 0 ? '+' : '') + fmt(d) : '—'}
                </td>
            </tr>`
        }).join("")
    }
}