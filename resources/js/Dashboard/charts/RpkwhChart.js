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

    // ─── KWH (merged dari PenjualanKwhChart) ─────────────────────────────────

    /**
     * Chart batang Target vs Realisasi KWH (4 dataset: 2 tahun).
     * @param {Array}   data  – array hasil getKwhData()
     * @param {boolean} akum  – mode akumulasi
     */
    renderKwhTargetRealisasi(data, akum) {
        this.make('chartTargetRealisasi', 'bar', LABELS, [
            { label: 'Target 2024',    data: data.map(d => akum ? d.targetAkum2024    : d.target2024),    backgroundColor: '#555'    },
            { label: 'Realisasi 2024', data: data.map(d => akum ? d.realisasiAkum2024 : d.realisasi2024), backgroundColor: '#2196f3' },
            { label: 'Target 2025',    data: data.map(d => akum ? d.targetAkum2025    : d.target2025),    backgroundColor: '#ff9800' },
            { label: 'Realisasi 2025', data: data.map(d => akum ? d.realisasiAkum2025 : d.realisasi2025), backgroundColor: '#00ff88' },
        ], v => (v / 1_000_000).toFixed(2) + ' GWh')
    }

    /**
     * Chart batang Delta KWH (positif hijau, negatif merah).
     * @param {Array}   data
     * @param {boolean} akum
     */
    renderKwhDelta(data, akum) {
    const values = data.map(d => akum ? d.deltaAkumKwh : d.deltaKwh);

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
            { label: '% Pencapaian Target',    data: data.map(d => d.pencapaianTarget * 100), borderColor: '#ff9800', tension: 0.4, fill: false, pointRadius: 4 },
            { label: '% Pencapaian Realisasi', data: data.map(d => d.pertumbuhanReal * 100),  borderColor: '#00ff88', tension: 0.4, fill: false, pointRadius: 4 },
        ], v => v.toFixed(1) + '%')
    }

    /**
     * Chart garis pertumbuhan (4 seri: target bulanan/akumulasi, realisasi bulanan/akumulasi).
     * @param {Array} data
     */
    renderKwhPertumbuhan(data) {
        this.make('chartPertumbuhan', 'line', LABELS, [
            { label: 'Target Bulanan',      data: data.map(d => d.pertumbuhanTarget     * 100), borderColor: '#888',    tension: 0.4, fill: false, pointRadius: 3 },
            { label: 'Target Akumulasi',    data: data.map(d => d.pertumbuhanTargetAkum * 100), borderColor: '#2196f3', tension: 0.4, fill: false, pointRadius: 3 },
            { label: 'Realisasi Bulanan',   data: data.map(d => d.pertumbuhanReal       * 100), borderColor: '#ff9800', tension: 0.4, fill: false, pointRadius: 3 },
            { label: 'Realisasi Akumulasi', data: data.map(d => d.pertumbuhanRealAkum   * 100), borderColor: '#00ff88', tension: 0.4, fill: false, pointRadius: 3 },
        ], v => v.toFixed(1) + '%')
    }
}