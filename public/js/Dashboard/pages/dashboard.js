import SheetsService from "../services/SheetsService.js"
import BppPage from "./bpp.js"
import PenjualanPage from "./penjualan.js"
import RealisasiKWHService from "../services/RealisasiKWHService.js"
import PenjualanKwhChart from "../charts/PenjualanKwhChart.js"
import PendapatanBatamService from "../services/PendapatanBatamService.js"
import PendapatanBatamChart from "../charts/PendapatanBatamChart.js"
import GolTarifBatamService from "../services/GolTarifBatamService.js"
import GolTarifBatamChart from "../charts/GolTarifBatamChart.js"

const SCRIPT_URL = "https://script.google.com/macros/s/AKfycbyeCM8lIbWi8VPResllz6EeOdTimhbbqEGuvZ6AyKlfNf2LT-IdyI2LpP8BFEZFSuhTqg/exec"

class Dashboard {
    constructor() {
        const sheets = new SheetsService(SCRIPT_URL)
        const labels = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"]

        // Page Components
        this.bpp = new BppPage(sheets, labels)
        this.penjualan = new PenjualanPage(sheets, labels)

        // kWh Services & Charts
        this.kwhService = new RealisasiKWHService(sheets)
        this.kwhChart = new PenjualanKwhChart()
        this.kwhData = null
        this.currentViewKwh = "bulanan"

        // Pendapatan Services & Charts
        this.pendapatanBatamService = new PendapatanBatamService(sheets)
        this.pendapatanBatamChart = new PendapatanBatamChart()
        this.pendapatanBatamData = null
        this.currentViewPendapatan = "bulanan"
        this.currentTarifPendapatan = "JUMLAH"
        //gol tarif batam
        this.golTarifBatamService = new GolTarifBatamService(sheets)
        this.golTarifBatamChart = new GolTarifBatamChart()
        this.golTarifBatamData = null
        this.currentTahunGol = "2025"
        this.currentViewGol = "bulanan"
        this.currentBulanGol = "11"

        // Global State
        this.currentMonth = "all"
        this.currentYear = "2025"
        this.labels = labels
    }

    async init() {
        try {
            console.log("Loading Dashboard...")

            // 1. Load BPP & Penjualan
            await Promise.all([
                this.bpp.load(),
                this.penjualan.load()
            ])

            this.bpp.render(this.currentMonth)
            this.penjualan.render(this.currentMonth)

            // 2. Load kWh Data
            this.kwhData = await this.kwhService.getData()
            this.renderKwh()

            // 3. Load Pendapatan Data dan gol tarif (Pindahkan ke dalam init)
            this.pendapatanBatamData = await this.pendapatanBatamService.getData()
            this.renderPendapatanBatam()
            this.golTarifBatamData = await this.golTarifBatamService.getData()
            this.renderGolTarifBatam()
            // 4. Initialize All Listeners
            this.initEventListeners()
            
            // 5. Start Clock
            this.updateClock()
            setInterval(() => this.updateClock(), 1000)
            

        } catch (err) {
            console.error("Dashboard Init Error:", err)
        }
    }

    initEventListeners() {
        // Filter Global (Bulan/Tahun)
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

        // Filter Grafik kWh
        document.getElementById("filterViewKwh")?.addEventListener("change", e => {
            this.currentViewKwh = e.target.value
            this.renderKwh()
        })

        // Filter Grafik Pendapatan
        document.getElementById("filterViewPendapatan")?.addEventListener("change", e => {
            this.currentViewPendapatan = e.target.value
            this.renderPendapatanBatam()
        })

        document.getElementById("filterTarifPendapatan")?.addEventListener("change", e => {
            this.currentTarifPendapatan = e.target.value
            this.renderPendapatanBatam()
        })
        document.getElementById("filterTahunPie")?.addEventListener("change", e => {
             this.currentTahunGol = e.target.value
             this.renderGolTarifBatam()
        })
        document.getElementById("filterViewPie")?.addEventListener("change", e => {
             this.currentViewGol = e.target.value
             this.renderGolTarifBatam()
        })
document.getElementById("filterBulanPie")?.addEventListener("change", e => {
    this.currentBulanGol = e.target.value
    this.renderGolTarifBatam()
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
    const akum  = this.currentViewGol === "akumulasi"
    const idx   = parseInt(this.currentBulanGol)
    const is2024 = this.currentTahunGol === "2024"
    const COLORS = ["#ff9800","#00ff88","#2196f3","#e91e63","#9c27b0","#00bcd4"]
    const labels = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]

    const nilaiPerGolongan = this.golTarifBatamData.golongan.map(g => {
        if (is2024) return akum ? this.golTarifBatamData.realisasi2024Akum[g][idx] : this.golTarifBatamData.realisasi2024Bul[g][idx]
        else        return akum ? this.golTarifBatamData.target2025Akum[g][idx]    : this.golTarifBatamData.target2025Bul[g][idx]
    })

    document.getElementById("judulPie").textContent =
        (is2024 ? "Realisasi" : "Target") + " Pendapatan " + this.currentTahunGol +
        (akum ? " (Akumulasi)" : " (Bulanan)") + " — " + labels[idx]

    this.golTarifBatamChart.pie(
        "chartPieTarif",
        this.golTarifBatamData.golongan.map(g => g.replace(/ \(.\) ?/, '')),
        nilaiPerGolongan,
        COLORS
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

    // Tabel realisasi
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

    // Tabel total
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
    updateClock() {
        const clockEl = document.getElementById("clock")
        if (!clockEl) return
        const now = new Date()
        const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }
        clockEl.textContent = `${now.toLocaleDateString('id-ID', options)} | ${now.toLocaleTimeString()}`
    }
}

// Inisialisasi
document.addEventListener("DOMContentLoaded", () => {
    const app = new Dashboard()
    app.init()
})