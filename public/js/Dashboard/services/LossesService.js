export default class LossesService {
    constructor(sheetsService) { this.sheets = sheetsService }

    async getData() {
        try {
            const withEmin    = await this.sheets.getSheet("02.Losses + EMIN")
            const withoutEmin = await this.sheets.getSheet("02.Losses - EMIN")

            const parse = (sheet) => sheet.slice(4, 16).map(row => ({
                bulan:          row[1],
                prodBruto:      this.p(row[2]),
                psKit:          this.p(row[3]),
                prodNetto:      this.p(row[4]),
                lokoTransmisi:  this.p(row[5]),
                siapSalur:      this.p(row[6]),
                psGi:           this.p(row[7]),
                lossesKwh:      this.p(row[8]),
                lossesBulanan:  this.p(row[9]),
                lossesAkum:     this.p(row[10]),
                prodSiapSalur:  this.p(row[11]),
                interkoneksi:   this.p(row[12]),
                lokoDistribusi: this.p(row[13]),
                psDistribusi:   this.p(row[14]),
            }))

            return { withEmin: parse(withEmin), withoutEmin: parse(withoutEmin) }
        } catch (err) {
            console.error("Gagal load Losses:", err)
            return null
        }
    }

    p(v) {
        if (typeof v === 'number') return v
        if (!v || v === "-" || v === "") return 0
        return parseFloat(v.toString().replace(/\./g, '').replace(',', '.')) || 0
    }
}