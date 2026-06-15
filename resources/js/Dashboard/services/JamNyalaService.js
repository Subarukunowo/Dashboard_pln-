export default class JamNyalaService {
    constructor() {
        this.baseUrl = '/api/chart/jam-nyala';
    }

    async getData(tahun = new Date().getFullYear(), tipe = 'realisasi') {
        try {
            const res    = await fetch(`${this.baseUrl}?tahun=${tahun}&tipe=${tipe}`)
            const result = await res.json()
            return result.data
        } catch (err) {
            console.error('Gagal load JamNyala:', err)
            return null
        }
    }
}