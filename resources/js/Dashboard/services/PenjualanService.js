export default class PenjualanService {
    constructor() {
        this.baseUrl = '/api/chart/penjualan'
    }

    async getKinerjaData(tahun = new Date().getFullYear()) {
        try {
            const res    = await fetch(`${this.baseUrl}?tahun=${tahun}`)
            const result = await res.json()
            return result.data
        } catch (err) {
            console.error('Gagal Load Penjualan:', err)
            return null
        }
    }
}