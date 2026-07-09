import BaseChart from './BaseChart.js'

const LABELS = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']

export default class RpkwhChart extends BaseChart {

    // ─── RPKWH (existing) ────────────────────────────────────────────────────

    bppVsHjr(id, bppSeries, hjrSeries, labels) {
        this.make(id, 'line', labels, [
            {
                label           : 'HJR (Harga Jual Rata-rata)',
                data            : hjrSeries,
                borderColor     : '#2196f3',
                backgroundColor : 'rgba(33,150,243,0.1)',
                tension         : 0.4,
                fill            : true,
                pointRadius     : 3,
            },
            {
                label           : 'BPP (Biaya Pokok Produksi)',
                data            : bppSeries,
                borderColor     : '#ff9800',
                backgroundColor : 'rgba(255,152,0,0.1)',
                tension         : 0.4,
                fill            : true,
                pointRadius     : 3,
            },
        ], v => v.toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' Rp/kWh')
    }

    // ─── KWH ──────────────────────────────────────────────────────────────
    // Field data mengikuti nama asli dari backend (buildAnalisa):
    // targetLalu, akumLalu, realisasiLalu, realisasiAkumLalu,
    // targetIni,  akumIni,  realisasiIni,  realisasiAkumIni,
    // deltaRpkwh, deltaAkum, pencapaianTarget, pencapaianRealisasi

    /**
     * Chart batang Target vs Realisasi KWH (4 dataset: tahun lalu vs ini).
     * @param {Array}   data
     * @param {boolean} akum
     * @param {number|string} tahunIni
     * @param {number|string} tahunLalu
     */
    renderKwhTargetRealisasi(data, akum, tahunIni, tahunLalu) {
        this.make('chartTargetRealisasi', 'bar', LABELS, [
            { label: `Target ${tahunLalu}`,    data: data.map(d => akum ? d.akumLalu          : d.targetLalu),       backgroundColor: '#555'    },
            { label: `Realisasi ${tahunLalu}`, data: data.map(d => akum ? d.realisasiAkumLalu : d.realisasiLalu),    backgroundColor: '#2196f3' },
            { label: `Target ${tahunIni}`,     data: data.map(d => akum ? d.akumIni           : d.targetIni),       backgroundColor: '#ff9800' },
            { label: `Realisasi ${tahunIni}`,  data: data.map(d => akum ? d.realisasiAkumIni  : d.realisasiIni),    backgroundColor: '#00ff88' },
        ], v => (v / 1_000_000).toFixed(2) + ' GWh')
    }

    /**
     * Chart batang Delta KWH (positif hijau, negatif merah).
     * @param {Array}   data
     * @param {boolean} akum
     */
    renderKwhDelta(data, akum) {
        const getValue = d => akum ? d.deltaAkum : d.deltaRpkwh
        const values = data.map(getValue)

        this.make('chartDelta', 'bar', LABELS, [
            {
                label           : 'Delta KWH',
                data            : values,
                backgroundColor : values.map(v => v >= 0 ? '#00ff88' : '#f44336'),
            },
        ], v => (v / 1_000_000).toFixed(2) + ' GWh')
    }

    /**
     * Chart garis % Pencapaian Target dan Realisasi.
     * @param {Array} data
     */
    renderKwhPencapaian(data) {
        this.make('chartPencapaian', 'line', LABELS, [
            { label: '% Growth Target',    data: data.map(d => ((d.pencapaianTarget    ?? 1) - 1) * 100), borderColor: '#ff9800', tension: 0.4, fill: false, pointRadius: 4 },
            { label: '% Growth Realisasi', data: data.map(d => ((d.pencapaianRealisasi ?? 1) - 1) * 100), borderColor: '#00ff88', tension: 0.4, fill: false, pointRadius: 4 },
        ], v => v.toFixed(1) + '%')
    }

    /**
     * Chart garis pertumbuhan (dihitung di frontend, tidak perlu field baru dari backend).
     * growth = (nilaiIni - nilaiLalu) / nilaiLalu
     * @param {Array} data
     */
    renderKwhPertumbuhan(data) {
        // pencapaianTarget  = targetIni / targetLalu  (ratio)
        // pencapaianRealisasi = realisasiIni / realisasiLalu (ratio)
        // Convert ke % growth: (ratio - 1) * 100
        this.make('chartPertumbuhan', 'line', LABELS, [
            { label: 'Pertumbuhan Target',    data: data.map(d => ((d.pencapaianTarget    ?? 1) - 1) * 100), borderColor: '#888',    tension: 0.4, fill: false, pointRadius: 3 },
            { label: 'Pertumbuhan Realisasi', data: data.map(d => ((d.pencapaianRealisasi ?? 1) - 1) * 100), borderColor: '#00ff88', tension: 0.4, fill: false, pointRadius: 3 },
        ], v => v.toFixed(1) + '%')
    }
}