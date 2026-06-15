export default class P2tlService {
    constructor() {
        this.baseUrl = '/api/chart/p2tl';
    }

    async getData(tahun = new Date().getFullYear(), tipe = "realisasi") {
        try {
            const response = await fetch(`${this.baseUrl}?tahun=${tahun}&tipe=${tipe}`);
            const result   = await response.json();
            return result.data.data;
        } catch (error) {
            console.error("Gagal memuat data P2TL dari MySQL:", error);
            return null;
        }
    }
}