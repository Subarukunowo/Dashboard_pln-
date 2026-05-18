export default class TunggakanService {
    constructor(sheetsService) { this.sheets = sheetsService }

    async getData() {
        try {
            const sheet = await this.sheets.getSheet("KINERJA TUNGGAKAN")

            const data = sheet.slice(4, 16).map(row => ({
                bulan:          row[1],
                palBerjalan:    this.p(row[2]),
                palTunggakan:   this.p(row[3]),
                tsBerjalan:     this.p(row[4]),
                tsTunggakan:    this.p(row[5]),
                jumlahTunggakan:this.p(row[6]),
                pendapatan:     this.p(row[7]),
                ratioTunggakan: this.p(row[8]),
                ratioTarget:    this.p(row[14]),
            }))

            return { data }
        } catch (err) {
            console.error("Gagal load Tunggakan:", err)
            return null
        }
    }

    p(v) {
        if (typeof v === 'number') return v
        if (!v || v === "-" || v === "") return 0
        return parseFloat(v.toString().replace(/\./g, '').replace(',', '.')) || 0
    }
}