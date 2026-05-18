export default class KvaService {
    constructor(sheetsService) {
        this.sheets = sheetsService
    }

    async getData() {
        try {
            const rencana      = await this.sheets.getSheet("RENCANA KVA")
            const realisasi    = await this.sheets.getSheet("REALISASI KVA")
            const realisasiGol = await this.sheets.getSheet("REALISASI GOL TARIF KVA")
            const targetGol    = await this.sheets.getSheet("TARGET GOL TARIF KVA")
            const rkap         = await this.sheets.getSheet("RKAP")
            const perbandingan = await this.sheets.getSheet("PERBANDINGAN KVA")

            const TARIF = [
                "S-2 / 450 VA", "S-2 / 900 VA", "S-2 / 1300 VA", "S-2 / 2200 VA",
                "S-2 / > 2200 VA s.d 200 kVA", "S-3 / > 200 kVA", "Jumlah Sosial (S)",
                "R-1 / 450 VA", "R-1 / 900 VA", "R-1 / 1300 VA", "R-1 / 2200 VA",
                "R-2 / > 2200 s.d 6600 VA", "R-3 / > 6600 VA", "Jumlah Rumah Tangga (R)",
                "B-1 / 450 VA", "B-1 / 900 VA", "B-1 / 1300 VA", "B-1 / 2200 VA",
                "B-2 / > 2200 VA s.d 200 kVA", "B-3 / > 200 kVA", "Jumlah Bisnis (B)",
                "I-1/2200 s/d 14000 VA", "I-2/>14 s/d 200 kVA",
                "I-3/>200 kVA s/d 5.000 kVA", "Jumlah Industri (I)",
                "P-1 / 450 VA", "P-1 / 1300 VA", "P-1 / 2200 VA",
                "P-1 / > 2200 VA s.d 200 kVA", "P-2 / > 200 kVA", "P3", "Jumlah Publik (P)",
                "M / PS (Penerangan Sementara)", "MB/ Multi Guna Prabayar", "Jumlah Multiguna (M)",
                "JUMLAH"
            ]

            const GOLONGAN = [
                "Jumlah Sosial (S)", "Jumlah Rumah Tangga (R)", "Jumlah Bisnis (B)",
                "Jumlah Industri (I)", "Jumlah Publik (P)", "Jumlah Multiguna (M)"
            ]

            const findRow = (sheet, key) => sheet.find(r =>
                r[0] && r[0].toString().trim().toUpperCase() === key.trim().toUpperCase()
            )

            const rencanaData = {}
            const realisasiData = {}
            TARIF.forEach(t => {
                const rRow  = findRow(rencana, t)
                const rlRow = findRow(realisasi, t)
                rencanaData[t]   = rRow  ? rRow.slice(1, 13).map(v => this.p(v))  : new Array(12).fill(0)
                realisasiData[t] = rlRow ? rlRow.slice(1, 13).map(v => this.p(v)) : new Array(12).fill(0)
            })

            const realisasiGolAkum = {}
            GOLONGAN.forEach(g => {
                const row = findRow(realisasiGol, g)
                realisasiGolAkum[g] = row ? row.slice(1, 13).map(v => this.p(v)) : new Array(12).fill(0)
            })

            const targetGolAkum = {}
            GOLONGAN.forEach(g => {
                const row = findRow(targetGol, g)
                targetGolAkum[g] = row ? row.slice(1, 13).map(v => this.p(v)) : new Array(12).fill(0)
            })

            const GOLONGAN_RKAP = ["Sosial (S)", "Rumah Tangga (R)", "Bisnis (B)", "Industri (I)", "Publik (P)", "Multiguna (M)", "JUMLAH"]
            const rkapData = []
            GOLONGAN_RKAP.forEach(g => {
                const row = findRow(rkap, g)
                if (row) rkapData.push({
                    golongan:    g,
                    targetTw1:   this.p(row[1]),
                    targetTw2:   this.p(row[2]),
                    targetTw3:   this.p(row[3]),
                    targetTw4:   this.p(row[4]),
                    targetTotal: this.p(row[5]),
                    realTw1:     this.p(row[6]),
                    realTw2:     this.p(row[7]),
                    realTw3:     this.p(row[8]),
                    realTw4:     this.p(row[9]),
                    realTotal:   this.p(row[10]),
                    pencapaian:  this.p(row[11]),
                })
            })

            const perbandinganData = perbandingan.slice(3, 15).map(row => ({
                bulan:        row[0],
                target2024:   this.p(row[1]),
                akum2024:     this.p(row[2]),
                realisasi2024:this.p(row[3]),
                realisasiAkum2024: this.p(row[4]),
                target2025:   this.p(row[5]),
                akum2025:     this.p(row[6]),
                realisasi2025:this.p(row[7]),
                realisasiAkum2025: this.p(row[8]),
                deltaKva:     this.p(row[9]),
                deltaPct:     this.p(row[10]),
                deltaAkumKva: this.p(row[11]),
                deltaAkumPct: this.p(row[12]),
                pencapaianTarget:   this.p(row[13]),
                pencapaianRealisasi:this.p(row[14]),
            }))

            return { tarif: TARIF, golongan: GOLONGAN, rencanaData, realisasiData, realisasiGolAkum, targetGolAkum, rkapData, perbandinganData }
        } catch (err) {
            console.error("Gagal load KVA:", err)
            return null
        }
    }

    p(v) {
        if (typeof v === 'number') return v
        if (!v || v === "-" || v === "") return 0
        return parseFloat(v.toString().replace(/\./g, '').replace(',', '.')) || 0
    }
}