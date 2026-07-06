import { apiFetch } from '@/Dashboard/utils/apiClient'

export default class TunggakanService {
    
    baseUrl = '/api/chart/tunggakan'
    

    async getData(tahun = new Date().getFullYear()) {
        try {
            const res    = await apiFetch(`${this.baseUrl}?tahun=${tahun}`)
            const result = await res.json()
            return result.data
        } catch (error) {
            console.error('Gagal memuat data Tunggakan dari MySQL:', error)
            return null
        }
    }
}
