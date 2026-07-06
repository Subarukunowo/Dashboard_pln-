import { apiFetch } from '@/Dashboard/utils/apiClient'

export default class RealisasiKWHService {
    
    baseUrl = '/api/chart/rpkwh'
  
    async getData(tahun = new Date().getFullYear(), tahunLalu = null) {
        try {
            const tl     = tahunLalu ?? tahun - 1
            const res    = await apiFetch(`${this.baseUrl}?tahun=${tahun}&tahun_lalu=${tl}`)
            const result = await res.json()
            const a      = result.data?.analisaData
            if (!a) return null

            return a.map(d => ({
                bulan             : d.bulan,
                target2024        : d.target2024,
                targetAkum2024    : d.akum2024,
                realisasi2024     : d.realisasi2024,
                realisasiAkum2024 : d.realisasiAkum2024,
                target2025        : d.target2025,
                targetAkum2025    : d.akum2025,
                realisasi2025     : d.realisasi2025,
                realisasiAkum2025 : d.realisasiAkum2025,
                deltaKwh          : d.deltaRpkwh,
                deltaPersen       : d.deltaPct,
                deltaAkumKwh      : d.deltaAkum,
                deltaAkumPersen   : d.deltaAkumPct,
                pencapaianTarget  : d.pencapaianTarget,
                pertumbuhanReal   : d.pencapaianRealisasi - 1,
            }))
        } catch (err) {
            console.error('Gagal load RealisasiKWH:', err)
            return null
        }
    }
}
