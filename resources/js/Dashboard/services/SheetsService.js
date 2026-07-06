/**
 * SheetsService.js
 *
 * Single source of truth — satu fetch ke GAS untuk semua service.
 * Cache in-memory dengan TTL 10 menit.
 * Jika ada fetch yang sedang berjalan, semua request tunggu hasil yang sama.
 */
class SheetsService {

    constructor(url) {
        this.url          = url
        this._cache       = null
        this._cacheTime   = null
        this._fetchPromise = null
        this.TTL          = 10 * 60 * 1000  // 10 menit
    }

    async fetchData() {
        const now = Date.now()

        // Cache masih fresh — pakai langsung
        if (this._cache && this._cacheTime && (now - this._cacheTime < this.TTL)) {
            return this._cache
        }

        // Ada fetch yang sedang berjalan — tunggu hasilnya (jangan double fetch)
        if (this._fetchPromise) return this._fetchPromise

        // Mulai fetch baru
        this._fetchPromise = fetch(this.url)
            .then(res => res.text())
            .then(text => {
                try {
                    this._cache      = JSON.parse(text)
                    this._cacheTime  = Date.now()
                } catch (e) {
                    console.error('[SheetsService] Gagal parse JSON:', e)
                    console.log('[SheetsService] Raw (300 char):', text.substring(0, 300))
                    // Jika parse gagal tapi ada cache lama, pakai itu
                    if (!this._cache) this._cache = {}
                }
                this._fetchPromise = null
                return this._cache
            })
            .catch(err => {
                console.error('[SheetsService] Fetch gagal:', err)
                this._fetchPromise = null
                // Fallback ke cache lama jika ada
                if (this._cache) {
                    console.warn('[SheetsService] Pakai cache lama sebagai fallback')
                    return this._cache
                }
                return {}
            })

        return this._fetchPromise
    }

    async getSheet(sheetName) {
        const data = await this.fetchData()
        const sheet = data[sheetName]
        if (!sheet) {
            console.warn(`[SheetsService] Sheet "${sheetName}" tidak ditemukan`)
            return []
        }
        return sheet
    }

    findRow(sheet, keyword) {
        return sheet.find(row => row[0] === keyword)
    }

    extractMonths(row, start = 6, total = 6) {
        return row.slice(start, start + total)
                  .map(v => Number.parseFloat(v) || 0)
    }

    /** Paksa invalidate cache — dipakai saat tahu data sudah berubah */
    invalidate() {
        this._cache      = null
        this._cacheTime  = null
        this._fetchPromise = null
        console.info('[SheetsService] Cache invalidated')
    }
}

export default SheetsService