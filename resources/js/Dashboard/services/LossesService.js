export default class LossesService {
    constructor(sheetsService) { this.sheets = sheetsService }

    async getData() {
        try {
            const withEmin    = await this.sheets.getSheet("02.Losses + EMIN")
            const withoutEmin = await this.sheets.getSheet("02.Losses - EMIN")

          const parse = (sheet) => sheet.slice(4, 16).map(row => ({
    bulan:                   row[1],
    prodBruto:               this.p(row[2]),
    psKit:                   this.p(row[3]),
    prodNetto:               this.p(row[4]),
    lokoTransmisi:           this.p(row[5]),
    siapSalur:               this.p(row[6]),
    psGi:                    this.p(row[7]),
    lossesKwh:               this.p(row[8]),
    lossesBulanan:           this.p(row[9]),
    lossesAkum:              this.p(row[10]),
    prodSiapSalur:           this.p(row[11]),
    interkoneksi:            this.p(row[12]),
    lokoDistribusi:          this.p(row[13]),
    psDistribusi:            this.p(row[14]),
    blkgPadang:              this.p(row[15]),
    sembulang:               this.p(row[16]),
    buluh:                   this.p(row[17]),
    pNguan:                  this.p(row[18]),
    penjualanTul:            this.p(row[19]),
    eminSistem:              this.p(row[20]),
    lossesDistribusiKwh:     this.p(row[21]),
    lossesDistribusiBulanan: this.p(row[22]),
    lossesDistribusiAkum:    this.p(row[23]),
    lossesJaringanKwh:       this.p(row[24]),
    lossesJaringanBulanan:   this.p(row[25]),
    lossesJaringanAkum:      this.p(row[26]),
    lossesTarget:            this.p(row[27]),
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