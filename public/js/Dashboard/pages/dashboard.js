import SheetsService from "../services/SheetsService.js"
import BppPage from "./bpp.js"
import PenjualanPage from "./penjualan.js"
import RealisasiKWHService from "../services/RealisasiKWHService.js"
import PenjualanKwhChart from "../charts/PenjualanKwhChart.js"
import PendapatanBatamService from "../services/PendapatanBatamService.js"
import PendapatanBatamChart from "../charts/PendapatanBatamChart.js"
import GolTarifBatamService from "../services/GolTarifBatamService.js"
import GolTarifBatamChart from "../charts/GolTarifBatamChart.js"
import RpkwhService from "../services/RpkwhService.js"
import RpkwhChart from "../charts/RpkwhChart.js"

const SCRIPT_URL = "https://script.google.com/macros/s/AKfycbyeCM8lIbWi8VPResllz6EeOdTimhbbqEGuvZ6AyKlfNf2LT-IdyI2LpP8BFEZFSuhTqg/exec"

class Dashboard {
    constructor() {
        const sheets = new SheetsService(SCRIPT_URL)
        const labels = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"]

        this.bpp = new BppPage(sheets, labels)
        this.penjualan = new PenjualanPage(sheets, labels)

        this.kwhService = new RealisasiKWHService(sheets)
        this.kwhChart = new PenjualanKwhChart()
        this.kwhData = null
        this.currentViewKwh = "bulanan"

        this.pendapatanBatamService = new PendapatanBatamService(sheets)
        this.pendapatanBatamChart = new PendapatanBatamChart()
        this.pendapatanBatamData = null
        this.currentViewPendapatan = "bulanan"
        this.currentTarifPendapatan = "JUMLAH"

        this.golTarifBatamService = new GolTarifBatamService(sheets)
        this.golTarifBatamChart = new GolTarifBatamChart()
        this.golTarifBatamData = null
        this.currentTahunGol = "2025"
        this.currentViewGol = "bulanan"
        this.currentBulanGol = "11"

        this.rpkwhService = new RpkwhService(sheets)
        this.rpkwhChart = new RpkwhChart()
        this.rpkwhData = null
        this.currentTarifRpkwh = "JUMLAH"
        this.currentViewRpkwh = "bulanan"
        this.currentBulanRpkwh = "11"
        this.currentTahunRpkwh = "2025"

        this.currentMonth = "all"
        this.currentYear = "2025"
        this.labels = labels
    }

    async init() {
        try {
            console.log("Loading Dashboard...")
            await Promise.all([this.bpp.load(), this.penjualan.load()])
            this.bpp.render(this.currentMonth)
            this.penjualan.render(this.currentMonth)

            this.kwhData = await this.kwhService.getData()
            this.renderKwh()

            this.pendapatanBatamData = await this.pendapatanBatamService.getData()
            this.renderPendapatanBatam()

            this.golTarifBatamData = await this.golTarifBatamService.getData()
            this.renderGolTarifBatam()

            this.rpkwhData = await this.rpkwhService.getData()
            this.renderRpkwh()

            this.initEventListeners()
            this.updateClock()
            setInterval(() => this.updateClock(), 1000)
        } catch (err) {
            console.error("Dashboard Init Error:", err)
        }
    }

    initEventListeners() {
        document.getElementById("filterMonth")?.addEventListener("change", (e) => {
            this.currentMonth = e.target.value
            this.bpp.render(this.currentMonth)
            this.penjualan.render(this.currentMonth)
        })
        document.getElementById("filterYear")?.addEventListener("change", (e) => {
            this.currentYear = e.target.value
            this.bpp.render(this.currentMonth)
            this.penjualan.render(this.currentMonth)
        })
        document.getElementById("filterViewKwh")?.addEventListener("change", e => {
            this.currentViewKwh = e.target.value; this.renderKwh()
        })
        document.getElementById("filterViewPendapatan")?.addEventListener("change", e => {
            this.currentViewPendapatan = e.target.value; this.renderPendapatanBatam()
        })
        document.getElementById("filterTarifPendapatan")?.addEventListener("change", e => {
            this.currentTarifPendapatan = e.target.value; this.renderPendapatanBatam()
        })
        document.getElementById("filterTahunPie")?.addEventListener("change", e => {
            this.currentTahunGol = e.target.value; this.renderGolTarifBatam()
        })
        document.getElementById("filterViewPie")?.addEventListener("change", e => {
            this.currentViewGol = e.target.value; this.renderGolTarifBatam()
        })
        document.getElementById("filterBulanPie")?.addEventListener("change", e => {
            this.currentBulanGol = e.target.value; this.renderGolTarifBatam()
        })
        document.getElementById("filterTarifRpkwh")?.addEventListener("change", e => {
            this.currentTarifRpkwh = e.target.value; this.renderRpkwh()
        })
        document.getElementById("filterViewRpkwh")?.addEventListener("change", e => {
            this.currentViewRpkwh = e.target.value; this.renderRpkwh()
        })
        document.getElementById("filterBulanRpkwh")?.addEventListener("change", e => {
            this.currentBulanRpkwh = e.target.value; this.renderRpkwh()
        })
        document.getElementById("filterTahunRpkwh")?.addEventListener("change", e => {
            this.currentTahunRpkwh = e.target.value; this.renderRpkwh()
        })
    }

    renderKwh() {
        if (!this.kwhData) return
        const akum = this.currentViewKwh === "akumulasi"
        this.kwhChart.renderTargetRealisasi(this.kwhData, akum)
        this.kwhChart.renderDelta(this.kwhData, akum)
        this.kwhChart.renderPencapaian(this.kwhData)
        this.kwhChart.renderPertumbuhan(this.kwhData)
        this.renderKwhTable(akum)
    }

    renderKwhTable(akum) {
        const tbody = document.getElementById("tabelRingkasan")
        if (!tbody) return
        const fmt = v => (v / 1_000_000).toFixed(2)
        const fmtPct = v => (v * 100).toFixed(2) + "%"
        tbody.innerHTML = this.kwhData.map(d => {
            const deltaValue = akum ? d.deltaAkumKwh : d.deltaKwh
            const deltaPersen = akum ? (d.deltaAkumPersen - 1) : (d.deltaPersen - 1)
            const color = deltaValue >= 0 ? '#00ff88' : '#f44336'
            return `
                <tr>
                    <td>${d.bulan}</td>
                    <td>${fmt(akum ? d.targetAkum2024 : d.target2024)}</td>
                    <td>${fmt(akum ? d.realisasiAkum2024 : d.realisasi2024)}</td>
                    <td>${fmt(akum ? d.targetAkum2025 : d.target2025)}</td>
                    <td>${fmt(akum ? d.realisasiAkum2025 : d.realisasi2025)}</td>
                    <td style="color:${color}">${fmt(deltaValue)}</td>
                    <td style="color:${color}">${fmtPct(deltaPersen)}</td>
                    <td>${fmtPct(d.pencapaianTarget)}</td>
                    <td>${fmtPct(d.pertumbuhanReal)}</td>
                </tr>
            `
        }).join("")
    }

    renderPendapatanBatam() {
        if (!this.pendapatanBatamData) return
        this.pendapatanBatamChart.render(
            this.pendapatanBatamData,
            this.currentViewPendapatan,
            this.currentTarifPendapatan
        )
        const tbody = document.getElementById("tabelPendapatan")
        if (tbody && this.pendapatanBatamData.realisasiBulanan) {
            const fmt = v => "Rp " + (v / 1_000_000_000).toFixed(2) + " M"
            tbody.innerHTML = this.pendapatanBatamData.realisasiBulanan.map(row => `
                <tr>
                    <td>${row.bulan}</td>
                    <td>${fmt(row.target * 1_000_000)}</td>
                    <td>${fmt(row.realisasi * 1_000_000)}</td>
                    <td style="color:${row.delta >= 0 ? '#00ff88' : '#f44336'}">${fmt(row.delta * 1_000_000)}</td>
                    <td style="color:${row.delta >= 0 ? '#00ff88' : '#f44336'}">${(row.pencapaian * 100).toFixed(2)}%</td>
                </tr>
            `).join("")
        }
    }

    renderGolTarifBatam() {
        if (!this.golTarifBatamData) return
        const akum = this.currentViewGol === "akumulasi"
        const idx = parseInt(this.currentBulanGol)
        const is2024 = this.currentTahunGol === "2024"
        const COLORS = ["#ff9800","#00ff88","#2196f3","#e91e63","#9c27b0","#00bcd4"]
        const labels = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]

        const nilaiPerGolongan = this.golTarifBatamData.golongan.map(g =>
            is2024
                ? (akum ? this.golTarifBatamData.realisasi2024Akum[g][idx] : this.golTarifBatamData.realisasi2024Bul[g][idx])
                : (akum ? this.golTarifBatamData.target2025Akum[g][idx]    : this.golTarifBatamData.target2025Bul[g][idx])
        )

        document.getElementById("judulPie").textContent =
            (is2024 ? "Realisasi" : "Target") + " Pendapatan " + this.currentTahunGol +
            (akum ? " (Akumulasi)" : " (Bulanan)") + " — " + labels[idx]

        this.golTarifBatamChart.pie("chartPieTarif",
            this.golTarifBatamData.golongan.map(g => g.replace(/ \(.\) ?/, '')),
            nilaiPerGolongan, COLORS
        )
        this.golTarifBatamChart.bar("chartRealisasiBulanan", labels, [
            { label: "Target",    data: this.golTarifBatamData.realisasiBulanan.map(d => d.target),    backgroundColor: "#ff9800" },
            { label: "Realisasi", data: this.golTarifBatamData.realisasiBulanan.map(d => d.realisasi), backgroundColor: "#00ff88" },
        ], v => "Rp " + (v/1000).toFixed(0) + "M")

        const delta = this.golTarifBatamData.realisasiBulanan.map(d => d.delta)
        this.golTarifBatamChart.bar("chartDeltaRealisasi", labels, [
            { label: "Delta", data: delta, backgroundColor: delta.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ])

        const t = this.golTarifBatamData.total
        this.golTarifBatamChart.bar("chartTotal2024vs2025", t.map(d => d.bulan), [
            { label: "Target 2024",    data: t.map(d => d.target2024),    backgroundColor: "#555" },
            { label: "Realisasi 2024", data: t.map(d => d.realisasi2024), backgroundColor: "#2196f3" },
            { label: "Target 2025",    data: t.map(d => d.target2025),    backgroundColor: "#ff9800" },
            { label: "Realisasi 2025", data: t.map(d => d.realisasi2025), backgroundColor: "#00ff88" },
        ])

        const deltaTotal = t.map(d => d.deltaKwh)
        this.golTarifBatamChart.bar("chartTotalDelta", t.map(d => d.bulan), [
            { label: "Delta", data: deltaTotal, backgroundColor: deltaTotal.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ])
        this.golTarifBatamChart.line("chartTotalPertumbuhan", t.map(d => d.bulan), [
            { label: "% Target",    data: t.map(d => ((d.target2025/d.target2024)-1)*100),       borderColor: "#ff9800", tension: 0.4, fill: false, pointRadius: 3 },
            { label: "% Realisasi", data: t.map(d => ((d.realisasi2025/d.realisasi2024)-1)*100), borderColor: "#00ff88", tension: 0.4, fill: false, pointRadius: 3 },
        ])

        const tbodyR = document.getElementById("tabelRealisasi")
        if (tbodyR) {
            const fmt = v => "Rp " + (v/1000).toFixed(2) + " Jt"
            const fmtPct = v => (v * 100).toFixed(2) + "%"
            tbodyR.innerHTML = this.golTarifBatamData.realisasiBulanan.map(d => `
                <tr>
                    <td>${d.bulan}</td>
                    <td>${fmt(d.target)}</td>
                    <td>${fmt(d.realisasi)}</td>
                    <td style="color:${d.delta >= 0 ? '#00ff88' : '#f44336'}">${fmt(d.delta)}</td>
                    <td style="color:${d.delta >= 0 ? '#00ff88' : '#f44336'}">${fmtPct(d.pencapaian)}</td>
                </tr>
            `).join("")
        }

        const tbodyT = document.getElementById("tabelTotal")
        if (tbodyT) {
            const fmtT = v => "Rp " + (v/1_000_000_000).toFixed(2) + "T"
            const fmtPct = v => (v * 100).toFixed(2) + "%"
            tbodyT.innerHTML = t.map(d => `
                <tr>
                    <td>${d.bulan}</td>
                    <td>${fmtT(d.target2024)}</td>
                    <td>${fmtT(d.realisasi2024)}</td>
                    <td>${fmtT(d.target2025)}</td>
                    <td>${fmtT(d.realisasi2025)}</td>
                    <td style="color:${d.deltaKwh >= 0 ? '#00ff88' : '#f44336'}">${fmtT(d.deltaKwh)}</td>
                    <td style="color:${d.deltaKwh >= 0 ? '#00ff88' : '#f44336'}">${fmtPct(d.deltaPct - 1)}</td>
                </tr>
            `).join("")
        }
    }

    renderRpkwh() {
        if (!this.rpkwhData) return
        const akum = this.currentViewRpkwh === "akumulasi"
        const idx = parseInt(this.currentBulanRpkwh)
        const is2024 = this.currentTahunRpkwh === "2024"
        const COLORS = ["#ff9800","#00ff88","#2196f3","#e91e63","#9c27b0","#00bcd4"]
        const LABELS = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]

        const r  = this.rpkwhData.rencanaData[this.currentTarifRpkwh]   || new Array(12).fill(0)
        const rl = this.rpkwhData.realisasiData[this.currentTarifRpkwh] || new Array(12).fill(0)

        this.rpkwhChart.make("chartRencanaRealisasi", "bar", LABELS, [
            { label: "Rencana",   data: r,  backgroundColor: "#ff9800" },
            { label: "Realisasi", data: rl, backgroundColor: "#00ff88" },
        ], v => v.toFixed(0) + " Rp/kWh")

        const delta = rl.map((v, i) => v - r[i])
        this.rpkwhChart.make("chartDeltaRpkwh", "bar", LABELS, [
            { label: "Delta", data: delta, backgroundColor: delta.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ])

        const nilaiGol = this.rpkwhData.golongan.map(g =>
            is2024
                ? (akum ? this.rpkwhData.realisasiGolAkum[g][idx] : this.rpkwhData.realisasiGolBul[g][idx])
                : (akum ? this.rpkwhData.targetGolAkum[g][idx]    : this.rpkwhData.targetGolBul[g][idx])
        )
        const judulEl = document.getElementById("judulPieRpkwh")
        if (judulEl) judulEl.textContent =
            (is2024 ? "Realisasi" : "Target") + " RPKWH " + this.currentTahunRpkwh +
            (akum ? " (Akumulasi)" : " (Bulanan)") + " — " + LABELS[idx]
        this.rpkwhChart.pie("chartPieRpkwh",
            this.rpkwhData.golongan.map(g => g.replace(/ \(.\) ?/, '')),
            nilaiGol
        )

        this.rpkwhChart.make("chartGrowthRpkwh", "line", LABELS,
            this.rpkwhData.golongan.map((g, i) => ({
                label: g.replace(/ \(.\) ?/, ''),
                data: akum ? this.rpkwhData.growthAkum[g] : this.rpkwhData.growthBul[g],
                borderColor: COLORS[i], tension: 0.4, fill: false, pointRadius: 3
            })),
            v => (v * 100).toFixed(1) + "%"
        )

        const tarifBpp = Object.keys(this.rpkwhData.bppHjrData)
        this.rpkwhChart.make("chartBppHjr", "line", LABELS,
            tarifBpp.map((t, i) => ({
                label: t, data: this.rpkwhData.bppHjrData[t],
                borderColor: COLORS[i % COLORS.length], tension: 0.4, fill: false, pointRadius: 3
            })),
            v => v.toFixed(0) + " Rp/kWh"
        )

        const a = this.rpkwhData.analisaData
        this.rpkwhChart.make("chartAnalisa2024vs2025", "bar", a.map(d => d.bulan), [
            { label: "Target 2024",    data: a.map(d => d.target2024),    backgroundColor: "#555" },
            { label: "Realisasi 2024", data: a.map(d => d.realisasi2024), backgroundColor: "#2196f3" },
            { label: "Target 2025",    data: a.map(d => d.target2025),    backgroundColor: "#ff9800" },
            { label: "Realisasi 2025", data: a.map(d => d.realisasi2025), backgroundColor: "#00ff88" },
        ], v => v.toFixed(0) + " Rp/kWh")

        const deltaA = a.map(d => d.deltaRpkwh)
        this.rpkwhChart.make("chartAnalisaDelta", "bar", a.map(d => d.bulan), [
            { label: "Delta", data: deltaA, backgroundColor: deltaA.map(v => v >= 0 ? "#00ff88" : "#f44336") }
        ])

        const p = this.rpkwhData.persenData
        this.rpkwhChart.make("chartPersenRpkwh", "bar", p.map(d => d.bulan), [
            { label: "Target",    data: p.map(d => d.target),    backgroundColor: "#ff9800" },
            { label: "Realisasi", data: p.map(d => d.realisasi), backgroundColor: "#00ff88" },
        ], v => v.toFixed(0) + " Rp/kWh")

        const sel = document.getElementById("filterTarifRpkwh")
        if (sel && sel.options.length === 0 && this.rpkwhData.tarif) {
            this.rpkwhData.tarif.forEach(t => {
                const opt = document.createElement("option")
                opt.value = t; opt.textContent = t
                sel.appendChild(opt)
            })
            sel.value = this.currentTarifRpkwh
        }

        const tbodyA = document.getElementById("tabelAnalisa")
        if (tbodyA) {
            const fmtPct = v => (v * 100).toFixed(2) + "%"
            tbodyA.innerHTML = a.map(d => `
                <tr>
                    <td>${d.bulan}</td>
                    <td>${d.target2024.toFixed(2)}</td>
                    <td>${d.realisasi2024.toFixed(2)}</td>
                    <td>${d.target2025.toFixed(2)}</td>
                    <td>${d.realisasi2025.toFixed(2)}</td>
                    <td style="color:${d.deltaRpkwh >= 0 ? '#00ff88' : '#f44336'}">${d.deltaRpkwh.toFixed(2)}</td>
                    <td style="color:${d.deltaRpkwh >= 0 ? '#00ff88' : '#f44336'}">${fmtPct(d.deltaPct - 1)}</td>
                    <td>${fmtPct(d.pencapaianTarget)}</td>
                    <td>${fmtPct(d.pencapaianRealisasi)}</td>
                </tr>
            `).join("")
        }

        const tbodyP = document.getElementById("tabelPersen")
        if (tbodyP) {
            tbodyP.innerHTML = p.map(d => `
                <tr>
                    <td>${d.bulan}</td>
                    <td>${d.target.toFixed(2)}</td>
                    <td>${d.realisasi.toFixed(2)}</td>
                    <td style="color:${d.delta >= 0 ? '#00ff88' : '#f44336'}">${d.delta.toFixed(2)}</td>
                </tr>
            `).join("")
        }
    }

    updateClock() {
        const clockEl = document.getElementById("clock")
        if (!clockEl) return
        const now = new Date()
        const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }
        clockEl.textContent = `${now.toLocaleDateString('id-ID', options)} | ${now.toLocaleTimeString()}`
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const app = new Dashboard()
    app.init()
})