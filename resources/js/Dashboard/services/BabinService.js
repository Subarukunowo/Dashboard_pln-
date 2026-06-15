export default class BabinService {
    constructor() {
        this.baseUrl = '/api/chart/babin'
    }

    async getData(tahun = new Date().getFullYear(), metrik = 'kwh') {
        try {
            const res    = await fetch(`${this.baseUrl}?tahun=${tahun}&metrik=${metrik}`)
            const result = await res.json()
            return result.data
        } catch (err) {
            console.error('Gagal load Babin:', err)
            return null
        }
    }
}