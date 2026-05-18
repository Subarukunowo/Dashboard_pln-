import BaseChart from './BaseChart.js'

/**
 * RpkwhChart — extend BaseChart
 *
 * RpkwhModule memanggil:
 *   this.chart.make(id, type, labels, datasets, yFmt)  ← BaseChart.make()
 *   this.chart.pie(id, labels, data)                   ← BaseChart.pie()
 *   this.chart.bppVsHjr(id, bpp, hjr, labels)          ← method khusus di bawah
 */
export default class RpkwhChart extends BaseChart {
    /**
     * Chart khusus BPP vs HJR — dua line dengan area fill
     */
    bppVsHjr(id, bppSeries, hjrSeries, labels) {
        this.make(id, 'line', labels, [
            {
                label:           'HJR (Harga Jual Rata-rata)',
                data:            hjrSeries,
                borderColor:     '#2196f3',
                backgroundColor: 'rgba(33,150,243,0.1)',
                tension:         0.4,
                fill:            true,
                pointRadius:     3,
            },
            {
                label:           'BPP (Biaya Pokok Produksi)',
                data:            bppSeries,
                borderColor:     '#ff9800',
                backgroundColor: 'rgba(255,152,0,0.1)',
                tension:         0.4,
                fill:            true,
                pointRadius:     3,
            },
        ], v => v.toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' Rp/kWh')
    }
}