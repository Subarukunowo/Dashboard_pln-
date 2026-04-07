export default class P2tlService {
    constructor(sheetsService) { this.sheets = sheetsService }

    async getData() {
        try {
            const sheet = await this.sheets.getSheet("P2TL")

            const data = sheet.slice(1).map(row => ({
                no:           row[0],
                bulan:        row[1],
                pemeriksaan:  this.p(row[2]),
                temuanPelanggan: this.p(row[3]),
                temuanKwh:    this.p(row[4]),
                tagsus:       this.p(row[5]),
            })).filter(d => d.bulan)

            return { data }
        } catch (err) {
            console.error("Gagal load P2TL:", err)
            return null
        }
    }

    p(v) {
        if (typeof v === 'number') return v
        if (!v || v === "-" || v === "") return 0
        return parseFloat(v.toString().replace(/\./g, '').replace(',', '.')) || 0
    }
}