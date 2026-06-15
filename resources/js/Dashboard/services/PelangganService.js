export default class PelangganService {
    constructor() {
        this.baseUrl = '/api/chart/pelanggan';
    }

    async getData(tahun = new Date().getFullYear(), tahunLalu = null) {
        try {
            const tl = tahunLalu ?? tahun - 1;
            const response = await fetch(`${this.baseUrl}?tahun=${tahun}&tahun_lalu=${tl}`);
            const result    = await response.json();
            return result.data;
        } catch (error) {
            console.error("Gagal memuat data Pelanggan dari MySQL:", error);
            return null;
        }
    }

    async muatData(tahun, tipe) {
        const response = await fetch('/api/rt-fact-pelanggan-load', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tahun, tipe }),
        });
        return response.json();
    }

    async hapusData(tahun, tipe) {
        const response = await fetch(`/api/rt-fact-pelanggan-by-filter?tahun=${tahun}&tipe=${tipe}`, {
            method: 'DELETE',
        });
        return response.json();
    }
}