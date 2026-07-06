import { apiFetch } from '@/Dashboard/utils/apiClient'

export default class LossesService {
    baseUrl = '/api/chart/losses'

    async getData(tahun = new Date().getFullYear(), tipe = 'realisasi') {
        try {
            const res  = await apiFetch(`${this.baseUrl}?tahun=${tahun}&tipe=${tipe}`)
            if (!res.ok) throw new Error(`HTTP ${res.status}`)
            const json = await res.json()
            return json.data
        } catch (err) {
            console.error('Gagal load Losses:', err)
            return null
        }
    }
}