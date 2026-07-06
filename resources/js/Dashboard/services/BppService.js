import { apiFetch } from '@/Dashboard/utils/apiClient'

export default class BppService {
    baseUrl = '/api/chart/bpp'

    async getAllBpp(tahun = new Date().getFullYear()) {
        try {
            const res    = await apiFetch(`${this.baseUrl}?tahun=${tahun}`)
            const result = await res.json()
            return result.data
        } catch (error) {
            console.error('Gagal memuat data BPP dari MySQL:', error)
            return null
        }
    }

    async getHjrAvg(tahun = new Date().getFullYear()) {
        try {
            const res    = await apiFetch(`/api/chart/hjr?tahun=${tahun}`)
            const result = await res.json()
            return result.data
        } catch (error) {
            console.error('Gagal memuat data HJR:', error)
            return null
        }
    }

    /**
     * HJR dihitung otomatis: Pendapatan / Penjualan per bulan
     * @returns {{ calculated: number[], tahun_efektif: number, is_fallback: boolean }}
     */
    async getHjrCalculated(tahun = new Date().getFullYear()) {
        try {
            const res    = await apiFetch(`/api/chart/hjr/calculated?tahun=${tahun}`)
            const result = await res.json()
            return result.data
        } catch (error) {
            console.error('Gagal memuat data HJR calculated:', error)
            return null
        }
    }
}
