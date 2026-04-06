export default class RpkwhService {
    constructor(sheetsService) {
        this.sheets = sheetsService;
    }

    async getData() {
        try {
            const rencana         = await this.sheets.getSheet("RENCANA RPKWH");
            const realisasi       = await this.sheets.getSheet("REALISASI RPKWH");
            const targetGolongan  = await this.sheets.getSheet("TARGET GOLONGAN RPKWH");
            const realisasiGol    = await this.sheets.getSheet("REALISASI GOLONGAN RPKWH");
            const growth          = await this.sheets.getSheet("REALISASI GROWTH RPKWH");
            const bppHjr          = await this.sheets.getSheet("REALISASI BPP HJR RPKWH");
            const analisa         = await this.sheets.getSheet("ANALISA RPKWH");
            const persen          = await this.sheets.getSheet("%REALISASI RPKWH");

            const GOLONGAN = ["Sosial (S)", "Rumah Tangga (R)", "Bisnis (B)", "Industri (I)", "Publik (P)", "Multiguna (M)"]

            const getRow = (sheet, keyword) =>
                sheet.find(r => r[0] && r[0].toString().trim() === keyword)

            // RENCANA & REALISASI per tarif — bulanan col 1-12, akumulasi col 1-12 di blok bawah
            const rencanaData = {}
            const realisasiData = {}
            const TARIF_LIST = rencana.slice(3).map(r => r[0]).filter(Boolean)

            TARIF_LIST.forEach(tarif => {
                const rRow = getRow(rencana, tarif)
                const rlRow = getRow(realisasi, tarif)
                rencanaData[tarif]   = rRow  ? rRow.slice(1, 13).map(v => this.p(v))  : new Array(12).fill(0)
                realisasiData[tarif] = rlRow ? rlRow.slice(1, 13).map(v => this.p(v)) : new Array(12).fill(0)
            })

            // TARGET GOLONGAN — akumulasi col 1-12, bulanan col 15-26
            const targetGolAkum = {}
            const targetGolBul  = {}
            GOLONGAN.forEach(g => {
                const row = getRow(targetGolongan, g)
                targetGolAkum[g] = row ? row.slice(1, 13).map(v => this.p(v))  : new Array(12).fill(0)
                targetGolBul[g]  = row ? row.slice(15, 27).map(v => this.p(v)) : new Array(12).fill(0)
            })

            // REALISASI GOLONGAN 2024 — akumulasi col 1-12, bulanan col 15-26
            const realisasiGolAkum = {}
            const realisasiGolBul  = {}
            GOLONGAN.forEach(g => {
                const row = getRow(realisasiGol, g)
                realisasiGolAkum[g] = row ? row.slice(1, 13).map(v => this.p(v))  : new Array(12).fill(0)
                realisasiGolBul[g]  = row ? row.slice(15, 27).map(v => this.p(v)) : new Array(12).fill(0)
            })

            // GROWTH — akumulasi col 1-12, bulanan col 15-26
            const growthAkum = {}
            const growthBul  = {}
            GOLONGAN.forEach(g => {
                const row = getRow(growth, g)
                growthAkum[g] = row ? row.slice(1, 13).map(v => this.p(v))  : new Array(12).fill(0)
                growthBul[g]  = row ? row.slice(15, 27).map(v => this.p(v)) : new Array(12).fill(0)
            })

            // BPP HJR — row 3+, col 0=tarif, col 1-12=bulan
            const bppHjrData = {}
            bppHjr.slice(3).forEach(row => {
                if (row[0]) bppHjrData[row[0]] = row.slice(1, 13).map(v => this.p(v))
            })

            // ANALISA — row 3-14
            const analisaData = analisa.slice(3, 15).map(row => ({
                bulan:           row[0],
                target2024:      this.p(row[1]),
                akum2024:        this.p(row[2]),
                realisasi2024:   this.p(row[3]),
                realisasiAkum2024: this.p(row[4]),
                target2025:      this.p(row[5]),
                akum2025:        this.p(row[6]),
                realisasi2025:   this.p(row[7]),
                realisasiAkum2025: this.p(row[8]),
                deltaRpkwh:      this.p(row[9]),
                deltaPct:        this.p(row[10]),
                deltaAkum:       this.p(row[11]),
                deltaAkumPct:    this.p(row[12]),
                pencapaianTarget:    this.p(row[13]),
                pencapaianRealisasi: this.p(row[14]),
            }))

            // %REALISASI — row 3-14
            const persenData = persen.slice(3, 15).map(row => ({
                bulan:     row[0],
                target:    this.p(row[1]),
                realisasi: this.p(row[2]),
                delta:     this.p(row[3]),
            }))

            return {
                tarif: TARIF_LIST,
                golongan: GOLONGAN,
                rencanaData, realisasiData,
                targetGolAkum, targetGolBul,
                realisasiGolAkum, realisasiGolBul,
                growthAkum, growthBul,
                bppHjrData,
                analisaData,
                persenData,
            }
        } catch (err) {
            console.error("Gagal load RPKWH:", err)
            return null
        }
    }

    p(v) {
        if (typeof v === 'number') return v;
        if (!v || v === "-" || v === "") return 0;
        return parseFloat(v.toString().replace(/\./g, '').replace(',', '.')) || 0;
    }
}