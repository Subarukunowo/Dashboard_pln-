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
                targetLalu        : d.targetLalu,
                targetAkumLalu    : d.akumLalu,
                realisasiLalu     : d.realisasiLalu,
                realisasiAkumLalu : d.realisasiAkumLalu,
                targetIni         : d.targetIni,
                targetAkumIni     : d.akumIni,
                realisasiIni      : d.realisasiIni,
                realisasiAkumIni  : d.realisasiAkumIni,
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