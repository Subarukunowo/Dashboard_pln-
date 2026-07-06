import { apiFetch } from '@/Dashboard/utils/apiClient'

export default class BabinService {

    baseUrl = '/api/chart/babin'
    

    async getData(tahun = new Date().getFullYear(), metrik = 'kwh') {
        try {
            const res    = await apiFetch(`${this.baseUrl}?tahun=${tahun}&metrik=${metrik}`)
            const result = await res.json()
            return result.data
        } catch (err) {
            console.error('Gagal load Babin:', err)
            return null
        }
    }
}
