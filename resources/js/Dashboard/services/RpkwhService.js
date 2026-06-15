export default class RpkwhService {
    constructor() {
        this.baseUrl = '/api/chart/rpkwh'
    }

    async getData(tahun = new Date().getFullYear(), tahunLalu = null) {
        try {
            const tl       = tahunLalu ?? tahun - 1
            const response = await fetch(`${this.baseUrl}?tahun=${tahun}&tahun_lalu=${tl}`)
            const result   = await response.json()
            return result.data
        } catch (error) {
            console.error('Gagal memuat data RPKWH dari MySQL:', error)
            return null
        }
    }

    /**
     * Mengambil data yang sama dari endpoint RPKWH, lalu memetakan
     * field analisaData ke struktur yang dipakai oleh render KWH.
     * Menggantikan RealisasiKWHService sepenuhnya.
     */
    async getKwhData(tahun = new Date().getFullYear(), tahunLalu = null) {
        try {
            const tl       = tahunLalu ?? tahun - 1
            const response = await fetch(`${this.baseUrl}?tahun=${tahun}&tahun_lalu=${tl}`)
            const result   = await response.json()
            const a        = result.data?.analisaData
            if (!a) return null

            return a.map(d => ({
                bulan              : d.bulan,
                target2024         : d.target2024,
                targetAkum2024     : d.akum2024,
                realisasi2024      : d.realisasi2024,
                realisasiAkum2024  : d.realisasiAkum2024,
                target2025         : d.target2025,
                targetAkum2025     : d.akum2025,
                realisasi2025      : d.realisasi2025,
                realisasiAkum2025  : d.realisasiAkum2025,
                deltaKwh           : d.deltaRpkwh,
                deltaPersen        : d.deltaPct,
                deltaAkumKwh       : d.deltaAkum,
                deltaAkumPersen    : d.deltaAkumPct,
                pencapaianTarget   : d.pencapaianTarget,
                pertumbuhanReal    : d.pencapaianRealisasi - 1,
            }))
        } catch (err) {
            console.error('Gagal load KWH dari RPKWH endpoint:', err)
            return null
        }
    }

    async hapusData(tahun, tipe) {
        const response = await fetch(
            `/api/rt-fact-rpkwh-by-filter?tahun=${tahun}&tipe=${tipe}`,
            { method: 'DELETE' }
        )
        return response.json()
    }

    async muatData(tahun, tipe) {
        const response = await fetch(
            `/api/rt-fact-rpkwh-muat?tahun=${tahun}&tipe=${tipe}`,
            { method: 'POST' }
        )
        return response.json()
    }
}