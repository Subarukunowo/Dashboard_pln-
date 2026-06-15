export default class PendapatanBatamService {
    constructor() {
        this.baseUrl = '/api/chart/pendapatan'
    }

    async getData(tahun = new Date().getFullYear()) {
        try {
            const res    = await fetch(`${this.baseUrl}?tahun=${tahun}`)
            const result = await res.json()
            return result.data
        } catch (err) {
            console.error('Gagal load PendapatanBatam:', err)
            return null
        }
    }
}