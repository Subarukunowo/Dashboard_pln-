import PelangganService from "../services/PelangganService.js"
import PelangganChart from "../charts/PelangganChart.js"
import SheetsService from "../services/SheetsService.js"

const SCRIPT_URL = "https://script.google.com/macros/s/AKfycbyeCM8lIbWi8VPResllz6EeOdTimhbbqEGuvZ6AyKlfNf2LT-IdyI2LpP8BFEZFSuhTqg/exec"
const LABELS = ["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"]
const COLORS = ["#ff9800","#00ff88","#2196f3","#e91e63","#9c27b0","#00bcd4"]

let data = null
let currentTarif = "JUMLAH"
let currentBulan = 11
const chart = new PelangganChart()

function renderAll() {
    if (!data) return

    // Chart 1: Rencana vs Realisasi per tarif
    const r  = data.rencanaData[currentTarif]   || new Array(12).fill(0)
    const rl = data.realisasiData[currentTarif] || new Array(12).fill(0)
    chart.make("chartPelangganBar", "bar", LABELS, [
        { label: "Rencana",   data: r,  backgroundColor: "#ff9800" },
        { label: "Realisasi", data: rl, backgroundColor: "#00ff88" },
    ])

    // Chart 2: Delta
    const delta = rl.map((v, i) => v - r[i])
    chart.make("chartPelangganDelta", "bar", LABELS, [
        { label: "Delta", data: delta, backgroundColor: delta.map(v => v >= 0 ? "#00ff88" : "#f44336") }
    ])

    // Chart 3: Pie distribusi golongan per bulan
    const pieLabels = data.golongan.map(g => g.replace(/ \(.\) ?/, ''))
    const pieData   = data.golongan.map(g => data.targetGolAkum[g][currentBulan] || 0)
    chart.pie("chartPelangganPie", pieLabels, pieData)

    // Chart 4: Target vs Realisasi akumulasi per golongan (bulan terpilih)
    chart.make("chartPelangganGol", "bar", pieLabels, [
        { label: "Target 2025",    data: data.golongan.map(g => data.targetGolAkum[g][currentBulan]),    backgroundColor: "#ff9800" },
        { label: "Realisasi 2024", data: data.golongan.map(g => data.realisasiGolAkum[g][currentBulan]), backgroundColor: "#2196f3" },
    ])

    // Tabel RKAP
    const tbody = document.getElementById("tabelRkapPelanggan")
    if (tbody && data.rkapData) {
        tbody.innerHTML = data.rkapData.map(d => `
            <tr>
                <td>${d.golongan}</td>
                <td>${d.targetTw1}</td>
                <td>${d.targetTw2}</td>
                <td>${d.targetTw3}</td>
                <td>${d.targetTw4}</td>
                <td>${d.targetTotal}</td>
                <td>${d.realTw1}</td>
                <td>${d.realTw2}</td>
                <td>${d.realTw3}</td>
                <td>${d.realTw4}</td>
                <td>${d.realTotal}</td>
                <td style="color:${d.pencapaian >= 1 ? '#00ff88' : '#f44336'}">${(d.pencapaian * 100).toFixed(2)}%</td>
            </tr>
        `).join("")
    }
}

async function init() {
    const sheets  = new SheetsService(SCRIPT_URL)
    const service = new PelangganService(sheets)
    data = await service.getData()

    const sel = document.getElementById("filterTarifPelanggan")
    if (sel && data) {
        data.tarif.forEach(t => {
            const opt = document.createElement("option")
            opt.value = t; opt.textContent = t
            sel.appendChild(opt)
        })
        sel.value = "JUMLAH"
    }

    renderAll()

    document.getElementById("filterTarifPelanggan")?.addEventListener("change", e => {
        currentTarif = e.target.value; renderAll()
    })
    document.getElementById("filterBulanPelanggan")?.addEventListener("change", e => {
        currentBulan = parseInt(e.target.value); renderAll()
    })
}

document.addEventListener("DOMContentLoaded", init)