export default class TunggakanService {
    constructor() {
        this.baseUrl = '/api/chart/tunggakan';
    }

    async getData(tahun = new Date().getFullYear()) {
        try {
            const response = await fetch(`${this.baseUrl}?tahun=${tahun}`);
            const result   = await response.json();
            return result.data;
        } catch (error) {
            console.error("Gagal memuat data Tunggakan dari MySQL:", error);
            return null;
        }
    }

    async updateRow(id, payload) {
        const response = await fetch(`/api/rt-fact-tunggakan/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        return response.json();
    }

    async createTahun(tahun) {
        const bulanNama = ['JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI','JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOVEMBER','DESEMBER'];
        const promises  = bulanNama.map((_, i) =>
            fetch('/api/rt-fact-tunggakan', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tahun, bulan: i + 1, sumber: 'manual' }),
            })
        );
        return Promise.all(promises);
    }

    async hapusData(tahun) {
        const response = await fetch(`/api/rt-fact-tunggakan-by-filter?tahun=${tahun}`, {
            method: 'DELETE',
        });
        return response.json();
    }
}