export default class BabinService {
    constructor(sheetsService) { this.sheets = sheetsService }

    async getData() {
        try {
            const sheet = await this.sheets.getSheet("KINERJA PENJUALAN BABIN & WRKR")
            const allJual = await this.sheets.getSheet("all jual kwh")

            // INTERKONEKSI BATAM-BINTAN: row 4-15 = 2024 realisasi, row 18-29 = 2025 target/realisasi
            const babinReal2024  = sheet.slice(4, 16).map(r => ({ bulan: r[1], realisasi: this.p(r[2]) }))
            const babinTarget2025 = sheet.slice(18, 30).map(r => ({ bulan: r[1], target: this.p(r[2]), realisasi: this.p(r[3]) }))
            const babinAkum2025   = sheet.slice(32, 44).map(r => ({ bulan: r[1], target: this.p(r[2]), realisasi: this.p(r[3]) }))

            // SEMBULANG, BLK PDG, BULUH & NGUAN: row 60-71 = 2024, row 73-84 = 2025 bulanan
            const semReal2024   = sheet.slice(60, 72).map(r => ({ bulan: r[1], realisasi: this.p(r[2]) }))
            const semTarget2025 = sheet.slice(73, 85).map(r => ({ bulan: r[1], target: this.p(r[2]), realisasi: this.p(r[3]) }))
            const semAkum2025   = sheet.slice(86, 98).map(r => ({ bulan: r[1], target: this.p(r[2]), realisasi: this.p(r[3]) }))

            // all jual kwh: row 4-15, col 1=bulan, 2-5=interkoneksi 2024, 6=batam-bintan 2024, 7=kwh batam 2024
            // col 9-12=interkoneksi 2025, 13=batam-bintan 2025, 14=kwh batam 2025
            const allJualData = allJual.slice(4, 16).map(r => ({
                bulan:         r[1],
                blkPdg2024:    this.p(r[2]),
                sembulang2024: this.p(r[3]),
                buluh2024:     this.p(r[4]),
                nguan2024:     this.p(r[5]),
                batamBintan2024: this.p(r[6]),
                kwhBatam2024:  this.p(r[7]),
                totalBulanan2024: this.p(r[8]),
                blkPdg2025:    this.p(r[9]),
                sembulang2025: this.p(r[10]),
                buluh2025:     this.p(r[11]),
                nguan2025:     this.p(r[12]),
                batamBintan2025: this.p(r[13]),
                kwhBatam2025:  this.p(r[14]),
            }))

            return { babinReal2024, babinTarget2025, babinAkum2025, semReal2024, semTarget2025, semAkum2025, allJualData }
        } catch (err) {
            console.error("Gagal load Babin:", err)
            return null
        }
    }

    p(v) {
        if (typeof v === 'number') return v
        if (!v || v === "-" || v === "") return 0
        return parseFloat(v.toString().replace(/\./g, '').replace(',', '.')) || 0
    }
}