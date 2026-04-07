export default class JamNyalaService {
    constructor(sheetsService) { this.sheets = sheetsService }

    async getData() {
        try {
            const sheet = await this.sheets.getSheet("JAM NYALA")
            const TARIF = []
            const data = {}

            sheet.slice(3).forEach(row => {
                const tarif = row[2] ? row[2].toString().trim() : null
                if (!tarif) return
                TARIF.push(tarif)
                data[tarif] = row.slice(3, 15).map(v => this.p(v))
            })

            return { tarif: TARIF, data }
        } catch (err) {
            console.error("Gagal load JamNyala:", err)
            return null
        }
    }

    p(v) {
        if (typeof v === 'number') return v
        if (!v || v === "-" || v === "") return 0
        return parseFloat(v.toString().replace(/\./g, '').replace(',', '.')) || 0
    }
}