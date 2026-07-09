import BaseChart from './BaseChart.js'

const LABELS = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']

export default class PenjualanKwhChart extends BaseChart {
    renderTargetRealisasi(data, akum, tahunIni, tahunLalu) {
        this.make('chartTargetRealisasi', 'bar', LABELS, [
            { label: `Target ${tahunLalu}`,    data: data.map(d => akum ? d.akumLalu          : d.targetLalu),    backgroundColor: '#555'    },
            { label: `Realisasi ${tahunLalu}`, data: data.map(d => akum ? d.realisasiAkumLalu : d.realisasiLalu), backgroundColor: '#2196f3' },
            { label: `Target ${tahunIni}`,     data: data.map(d => akum ? d.akumIni           : d.targetIni),    backgroundColor: '#ff9800' },
            { label: `Realisasi ${tahunIni}`,  data: data.map(d => akum ? d.realisasiAkumIni  : d.realisasiIni), backgroundColor: '#00ff88' },
        ], v => (v / 1_000_000).toFixed(2) + ' GWh')
    }

    renderDelta(data, akum) {
        const getValue = d => akum ? d.deltaAkum : d.deltaRpkwh
        const values = data.map(getValue)

        this.make('chartDelta', 'bar', LABELS, [
            {
                label: 'Delta KWH',
                data:  values,
                backgroundColor: values.map(v => v >= 0 ? '#00ff88' : '#f44336'),
            },
        ], v => (v / 1_000_000).toFixed(2) + ' GWh')
    }

    renderPencapaian(data) {
        this.make('chartPencapaian', 'line', LABELS, [
            { label: '% Pencapaian Target',    data: data.map(d => (d.pencapaianTarget    ?? 0) * 100), borderColor: '#ff9800', tension: 0.4, fill: false, pointRadius: 4 },
            { label: '% Pencapaian Realisasi', data: data.map(d => (d.pencapaianRealisasi ?? 0) * 100), borderColor: '#00ff88', tension: 0.4, fill: false, pointRadius: 4 },
        ], v => v.toFixed(1) + '%')
    }

    // renderPertumbuhan dihapus — field pertumbuhanTarget/pertumbuhanTargetAkum/
    // pertumbuhanRealAkum belum ada di backend.
}