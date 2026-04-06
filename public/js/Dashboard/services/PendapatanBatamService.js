export default class PendapatanBatamService {
    constructor(sheetsService) {
        this.sheets = sheetsService;
    }

    async getData() {
        try {
            const rencana   = await this.sheets.getSheet("RENCANA PENDAPATAN BATAM");
            const realisasi = await this.sheets.getSheet("REALISASI PENDAPATAN BATAM");

            if (!rencana || !realisasi) return null;

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
            ];

            const findRow = (sheet, tarif) => sheet.find(r =>
                r[0] && r[0].toString().trim().toUpperCase() === tarif.trim().toUpperCase()
            );

            const result = {};
            TARIF.forEach(tarif => {
                const rBul  = findRow(rencana, tarif);
                const rlBul = findRow(realisasi, tarif);

                result[tarif] = {
                    rencana:       rBul  ? rBul.slice(1, 13).map(v => this.parseNumber(v))  : new Array(12).fill(0),
                    rencanaAkum:   new Array(12).fill(0),
                    realisasi:     rlBul ? rlBul.slice(1, 13).map(v => this.parseNumber(v)) : new Array(12).fill(0),
                    realisasiAkum: new Array(12).fill(0),
                };
            });

            const realisasiSheet = await this.sheets.getSheet("REALISASI ");
            const realisasiBulanan = realisasiSheet.slice(3, 15).map(row => ({
                bulan:      row[0],
                target:     this.parseNumber(row[1]),
                realisasi:  this.parseNumber(row[2]),
                delta:      this.parseNumber(row[3]),
                pencapaian: this.parseNumber(row[4]),
            }));

            return { tarif: TARIF, data: result, realisasiBulanan };
        } catch (err) {
            console.error("Gagal load PendapatanBatam:", err);
            return null;
        }
    }

    parseNumber(value) {
        if (typeof value === 'number') return value;
        if (!value || value === "-" || value === "" || value === " - ") return 0;
        let cleaned = value.toString().replace(/\./g, '').replace(',', '.');
        return parseFloat(cleaned) || 0;
    }
}