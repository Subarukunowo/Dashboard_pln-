export default class GolTarifBatamService {
    constructor(sheetsService) {
        this.sheets = sheetsService;
    }

    async getData() {
        try {
            const realisasiTarif = await this.sheets.getSheet("REALISASI PENDAPATAN TARIF");
            const targetTarif    = await this.sheets.getSheet("TARGET PENDAPATAN TARIF");
            const realisasi      = await this.sheets.getSheet("REALISASI ");
            const totalRealisasi = await this.sheets.getSheet("TOTAL REALISASI");

            const GOLONGAN = ["Sosial (S)", "Rumah Tangga (R)", "Bisnis (B)", "Industri (I)", "Publik (P)", "Multiguna (M)"]
            const BULAN_IDX = 12 // kolom 1-12 = Jan-Des

            // Helper ambil row per golongan
            const getRow = (sheet, keyword) =>
                sheet.find(r => r[0] && r[0].toString().trim().replace(/\s+/g, ' ') === keyword)

            // REALISASI TARIF 2024 - akumulasi (col 1-12) dan bulanan (col 15-26)
            const realisasi2024Akum = {}
            const realisasi2024Bul  = {}
            GOLONGAN.forEach(g => {
                const row = getRow(realisasiTarif, g)
                realisasi2024Akum[g] = row ? row.slice(1, 13).map(v => this.p(v)) : new Array(12).fill(0)
                realisasi2024Bul[g]  = row ? row.slice(15, 27).map(v => this.p(v)) : new Array(12).fill(0)
            })

            // TARGET TARIF 2025 - akumulasi (col 1-12) dan bulanan (col 15-26)
            const target2025Akum = {}
            const target2025Bul  = {}
            GOLONGAN.forEach(g => {
                const row = getRow(targetTarif, g)
                target2025Akum[g] = row ? row.slice(1, 13).map(v => this.p(v)) : new Array(12).fill(0)
                target2025Bul[g]  = row ? row.slice(15, 27).map(v => this.p(v)) : new Array(12).fill(0)
            })

            // REALISASI bulanan 2025 — row 3-14, col: Bln, Target, Realisasi, Delta, %Pencapaian
            const realisasiBulanan = realisasi.slice(3, 15).map(row => ({
                bulan:       row[0],
                target:      this.p(row[1]),
                realisasi:   this.p(row[2]),
                delta:       this.p(row[3]),
                pencapaian:  this.p(row[4]),
            }))

            // TOTAL REALISASI — row 3-14
            const total = totalRealisasi.slice(3, 15).map(row => ({
                bulan:           row[0],
                target2024:      this.p(row[1]),
                akum2024:        this.p(row[2]),
                realisasi2024:   this.p(row[3]),
                realisasiAkum2024: this.p(row[4]),
                target2025:      this.p(row[5]),
                akum2025:        this.p(row[6]),
                realisasi2025:   this.p(row[7]),
                realisasiAkum2025: this.p(row[8]),
                deltaKwh:        this.p(row[9]),
                deltaPct:        this.p(row[10]),
                deltaAkumKwh:    this.p(row[11]),
                deltaAkumPct:    this.p(row[12]),
            }))

            return {
                golongan: GOLONGAN,
                realisasi2024Akum, realisasi2024Bul,
                target2025Akum,    target2025Bul,
                realisasiBulanan,
                total
            }
        } catch (err) {
            console.error("Gagal load PendapatanTarif:", err)
            return null
        }
    }

    p(v) {
        if (typeof v === 'number') return v;
        if (!v || v === "-" || v === "") return 0;
        return parseFloat(v.toString().replace(/\./g, '').replace(',', '.')) || 0;
    }
}