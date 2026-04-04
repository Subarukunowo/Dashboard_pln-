export default class RealisasiKWHService {
    constructor(sheetsService) {
        this.sheets = sheetsService;
    }

    async getData() {
        try {
            const sheet = await this.sheets.getSheet("REALISASI PENJUALAN TENAGA LIST");
            if (!sheet || sheet.length < 6) return null;

            const rows = sheet.slice(5, 17); // Row 5-16 = Januari-Desember
            const bulan = ["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];

            return rows.map((row, i) => ({
                bulan: bulan[i],
                target2024:        this.parseNumber(row[1]),
                targetAkum2024:    this.parseNumber(row[2]),
                realisasi2024:     this.parseNumber(row[3]),
                realisasiAkum2024: this.parseNumber(row[4]),
                target2025:        this.parseNumber(row[5]),
                targetAkum2025:    this.parseNumber(row[6]),
                realisasi2025:     this.parseNumber(row[7]),
                realisasiAkum2025: this.parseNumber(row[8]),
                deltaKwh:          this.parseNumber(row[9]),
                deltaPersen:       this.parseNumber(row[10]),
                deltaAkumKwh:      this.parseNumber(row[11]),
                deltaAkumPersen:   this.parseNumber(row[12]),
                pencapaianTarget:  this.parseNumber(row[13]),
                pencapaianReal:    this.parseNumber(row[14]),
                delta:             this.parseNumber(row[15]),
                pertumbuhanTarget: this.parseNumber(row[16]),
                pertumbuhanTargetAkum:   this.parseNumber(row[17]),
                pertumbuhanReal:         this.parseNumber(row[18]),
                pertumbuhanRealAkum:     this.parseNumber(row[19]),
            }));
        } catch (err) {
            console.error("Gagal load RealisasiPenjualan:", err);
            return null;
        }
    }

    parseNumber(value) {
        if (typeof value === 'number') return value;
        if (!value || value === "-" || value === "") return 0;
        const clean = value.toString().replace(/\./g, '').replace(',', '.');
        return parseFloat(clean) || 0;
    }
}