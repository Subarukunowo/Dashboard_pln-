import { apiFetch } from '@/Dashboard/utils/apiClient'

export default class TunggakanService {
    baseUrl        = '/api/chart/tunggakan'
    crudUrl         = '/api/v1/fact/tunggakan'
    createTahunUrl  = '/api/v1/fact/tunggakan/create-tahun'
    deleteByYearUrl = '/api/rt-fact-tunggakan-by-filter'

    getHeaders() {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf,
        }
    }

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

    /**
     * Buat data tunggakan tahun baru (12 bulan x 2 tipe: realisasi & target).
     * Sekarang cukup 1 request ke endpoint create-tahun, backend yang
     * menangani idempotensi & default target ratio.
     */
    async createTahun(tahun) {
        const res = await apiFetch(this.createTahunUrl, {
            method:  'POST',
            headers: this.getHeaders(),
            body:    JSON.stringify({ tahun }),
        })

        if (!res.ok) {
            throw new Error(`Gagal membuat data tunggakan tahun ${tahun}`)
        }

        return true
    }

    /**
     * Hapus semua data tunggakan untuk satu tahun.
     * Endpoint ini terdaftar terpisah dari apiResource fact/tunggakan,
     * jadi path-nya BUKAN /api/v1/fact/tunggakan?tahun=...
     */
    async hapusData(tahun) {
        const res = await apiFetch(this.deleteByYearUrl, {
            method:  'DELETE',
            headers: this.getHeaders(),
            body:    JSON.stringify({ tahun }),
        })

        if (!res.ok) {
            throw new Error(`Gagal menghapus data tunggakan tahun ${tahun}`)
        }

        return true
    }
}