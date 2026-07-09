import { apiFetch } from '@/Dashboard/utils/apiClient'

export default class GolTarifBatamService {
    baseUrl = '/api/chart/pendapatan'

    async getData(tahun = new Date().getFullYear()) {
        try {
            const res    = await apiFetch(`${this.baseUrl}?tahun=${tahun}`)
            const result = await res.json()
            return result?.data ?? null
        } catch (err) {
            console.error('Gagal load GolTarifBatam:', err)
            return null
        }
    }
}