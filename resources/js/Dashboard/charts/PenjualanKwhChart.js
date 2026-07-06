import BaseChart from './BaseChart.js'

const LABELS = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']

export default class PenjualanKwhChart extends BaseChart {
    renderTargetRealisasi(data, akum) {
        this.make('chartTargetRealisasi', 'bar', LABELS, [
            { label: 'Target 2024',    data: data.map(d => akum ? d.targetAkum2024    : d.target2024),    backgroundColor: '#555'    },
            { label: 'Realisasi 2024', data: data.map(d => akum ? d.realisasiAkum2024 : d.realisasi2024), backgroundColor: '#2196f3' },
            { label: 'Target 2025',    data: data.map(d => akum ? d.targetAkum2025    : d.target2025),    backgroundColor: '#ff9800' },
            { label: 'Realisasi 2025', data: data.map(d => akum ? d.realisasiAkum2025 : d.realisasi2025), backgroundColor: '#00ff88' },
        ], v => (v / 1_000_000).toFixed(2) + ' GWh')
    }

renderDelta(data, akum) {
    const getValue = d => akum ? d.deltaAkumKwh : d.deltaKwh;

    this.make('chartDelta', 'bar', LABELS, [
        {
            label: 'Delta KWH',
            data:  data.map(getValue),
            backgroundColor: data.map(d => getValue(d) >= 0 ? '#00ff88' : '#f44336'),
        },
    ], v => (v / 1_000_000).toFixed(2) + ' GWh')
}

    renderPencapaian(data) {
        this.make('chartPencapaian', 'line', LABELS, [
            { label: '% Pencapaian Target',    data: data.map(d => d.pencapaianTarget * 100), borderColor: '#ff9800', tension: 0.4, fill: false, pointRadius: 4 },
            { label: '% Pencapaian Realisasi', data: data.map(d => d.pencapaianReal * 100),   borderColor: '#00ff88', tension: 0.4, fill: false, pointRadius: 4 },
        ], v => v.toFixed(1) + '%')
    }

    renderPertumbuhan(data) {
        this.make('chartPertumbuhan', 'line', LABELS, [
            { label: 'Target Bulanan',      data: data.map(d => d.pertumbuhanTarget * 100),     borderColor: '#888',    tension: 0.4, fill: false, pointRadius: 3 },
            { label: 'Target Akumulasi',    data: data.map(d => d.pertumbuhanTargetAkum * 100), borderColor: '#2196f3', tension: 0.4, fill: false, pointRadius: 3 },
            { label: 'Realisasi Bulanan',   data: data.map(d => d.pertumbuhanReal * 100),        borderColor: '#ff9800', tension: 0.4, fill: false, pointRadius: 3 },
            { label: 'Realisasi Akumulasi', data: data.map(d => d.pertumbuhanRealAkum * 100),    borderColor: '#00ff88', tension: 0.4, fill: false, pointRadius: 3 },
        ], v => v.toFixed(1) + '%')
    }
}