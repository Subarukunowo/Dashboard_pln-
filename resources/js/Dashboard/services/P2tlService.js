import { apiFetch } from '@/Dashboard/utils/apiClient'

export default class P2tlService {
    constructor() {
        this.baseUrl = '/api/chart/p2tl'
    }

    async getData(tahun = new Date().getFullYear(), tipe = 'realisasi') {
        try {
            const res    = await apiFetch(`${this.baseUrl}?tahun=${tahun}&tipe=${tipe}`)
            const result = await res.json()
            return result.data.data
        } catch (error) {
            console.error('Gagal memuat data P2TL dari MySQL:', error)
            return null
        }
    }
}
