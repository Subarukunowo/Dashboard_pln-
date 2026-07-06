import { apiFetch } from '@/Dashboard/utils/apiClient'

export default class GolTarifBatamService {
    baseUrl = '/api/chart/pendapatan'

    async getData(tahun = new Date().getFullYear()) {
        try {
            const res    = await apiFetch(`${this.baseUrl}?tahun=${tahun}`)
            const result = await res.json()
            const d      = result.data

            const GOLONGAN  = ['S', 'R', 'B', 'I', 'P', 'M']
            const golLabels = {
                S: 'Sosial (S)', R: 'Rumah Tangga (R)', B: 'Bisnis (B)',
                I: 'Industri (I)', P: 'Publik (P)', M: 'Multiguna (M)',
            }

            const realisasi2024Akum = {}
            const realisasi2024Bul  = {}
            const target2025Akum    = {}
            const target2025Bul     = {}

            GOLONGAN.forEach(g => {
                const label = golLabels[g]
                const gData = d.golongan[g]
                realisasi2024Bul[label]  = gData?.realisasi ?? new Array(12).fill(0)
                realisasi2024Akum[label] = gData?.realisasi ?? new Array(12).fill(0)
                target2025Bul[label]     = gData?.target    ?? new Array(12).fill(0)
                target2025Akum[label]    = gData?.target    ?? new Array(12).fill(0)
            })

            const realisasiBulanan = d.realisasiBulanan.map(r => ({
                bulan:      r.bulan,
                target:     r.target,
                realisasi:  r.realisasi,
                delta:      r.delta,
                pencapaian: r.pencapaian,
            }))

            const jumlahData = d.data['JUMLAH']
            const total = Array.from({ length: 12 }, (_, i) => ({
                bulan:         i + 1,
                target2024:    jumlahData?.rencana[i]         ?? 0,
                realisasi2024: jumlahData?.realisasi[i]       ?? 0,
                target2025:    jumlahData?.rencanaAkum[i]     ?? 0,
                realisasi2025: jumlahData?.realisasiAkum[i]   ?? 0,
                deltaKwh:      (jumlahData?.realisasi[i] ?? 0) - (jumlahData?.rencana[i] ?? 0),
                deltaPct:      (jumlahData?.rencana[i] ?? 0) > 0
                    ? (jumlahData?.realisasi[i] ?? 0) / (jumlahData?.rencana[i] ?? 0)
                    : 1,
            }))

            return {
                golongan: GOLONGAN.map(g => golLabels[g]),
                realisasi2024Akum,
                realisasi2024Bul,
                target2025Akum,
                target2025Bul,
                realisasiBulanan,
                total,
            }
        } catch (err) {
            console.error('Gagal load GolTarifBatam:', err)
            return null
        }
    }
}
