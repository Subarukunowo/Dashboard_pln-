export default class BppService {
    constructor() {
        this.baseUrl = '/api/chart/bpp';
    }

    async getAllBpp(tahun = new Date().getFullYear()) {
        try {
            const response = await fetch(`${this.baseUrl}?tahun=${tahun}`);
            const result = await response.json();
            return result.data; 
        } catch (error) {
            console.error("Gagal memuat data BPP dari MySQL:", error);
            return null;
        }
    }
    async getHjrAvg(tahun = new Date().getFullYear()) {
    try {
        const response = await fetch(`/api/chart/hjr?tahun=${tahun}`);
        const result   = await response.json();
        return result.data;
    } catch (error) {
        console.error("Gagal memuat data HJR:", error);
        return null;
    }
}
}