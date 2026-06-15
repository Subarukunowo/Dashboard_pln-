export default class KvaService {
    constructor() {
        this.baseUrl = '/api/chart/kva';
    }

    async getData(tahun = new Date().getFullYear(), tahunLalu = null) {
        try {
            const tl = tahunLalu ?? tahun - 1;
            const response = await fetch(`${this.baseUrl}?tahun=${tahun}&tahun_lalu=${tl}`);
            const result    = await response.json();
            return result.data;
        } catch (error) {
            console.error("Gagal memuat data KVA dari MySQL:", error);
            return null;
        }
    }
}