import BaseChart from './BaseChart.js'

/**
 * PendapatanBatamChart — extend BaseChart
 *
 * PendapatanBatamModule memanggil:
 *   this.chart.renderWithLabels(id, labels, {rencana, realisasi})
 *   this.chart.make(id, type, datasets, yCallback)   ← signature lama, perlu wrapper
 *
 * Kita pertahankan API lama agar PendapatanBatamModule tidak perlu diubah,
 * sambil mewarisi destroy() dari BaseChart.
 */
export default class PendapatanBatamChart extends BaseChart {

    /**
     * Dipakai oleh PendapatanBatamModule untuk chart delta (tanpa labels param)
     * Signature lama: make(id, type, datasets, yCallback)
     * Override untuk mendukung 2 signature:
     *   make(id, type, labels, datasets, yFmt)   ← BaseChart standard
     *   make(id, type, datasets, yCallback)       ← legacy PendapatanBatam
     */
    make(id, type, labelsOrDatasets, datasetsOrFmt = null, yFmt = null) {
        // Deteksi: apakah arg ke-3 adalah array of strings (labels) atau array of dataset objects
        const isLabels = Array.isArray(labelsOrDatasets) && (
            labelsOrDatasets.length === 0 ||
            typeof labelsOrDatasets[0] === 'string'
        )

        if (isLabels) {
            // Signature baru: make(id, type, labels, datasets, yFmt)
            super.make(id, type, labelsOrDatasets, datasetsOrFmt, yFmt)
        } else {
            // Signature lama: make(id, type, datasets, yCallback)
            // Gunakan label bulan standar
            const LABELS = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']
            super.make(id, type, LABELS, labelsOrDatasets, datasetsOrFmt)
        }
    }

    renderWithLabels(id, labels, { rencana, realisasi }) {
        super.make(id, 'bar', labels, [
            { label: 'Rencana',   data: rencana,   backgroundColor: '#ff9800' },
            { label: 'Realisasi', data: realisasi, backgroundColor: '#00ff88' },
        ], v => 'Rp ' + (v / 1_000_000_000).toFixed(2) + 'M')
    }

    renderBar(id, { rencana, realisasi }) {
        const LABELS = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']
        this.renderWithLabels(id, LABELS, { rencana, realisasi })
    }

    // make2 dipertahankan sebagai alias make untuk backward compat
    make2(id, type, labels, datasets, yCallback = null) {
        super.make(id, type, labels, datasets, yCallback)
    }
}