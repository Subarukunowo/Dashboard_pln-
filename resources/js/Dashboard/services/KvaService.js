import { apiFetch } from '@/Dashboard/utils/apiClient'

export default class KvaService {
    
    baseUrl = '/api/chart/kva'

    async getData(tahun = new Date().getFullYear(), tahunLalu = null) {
        try {
            const tl     = tahunLalu ?? tahun - 1
            const res    = await apiFetch(`${this.baseUrl}?tahun=${tahun}&tahun_lalu=${tl}`)
            const result = await res.json()
            return result.data
        } catch (error) {
            console.error('Gagal memuat data KVA dari MySQL:', error)
            return null
        }
    }
}
