export default class PenjualanService {
    constructor(sheetsService) {
        this.sheets = sheetsService;
    }

    async getKinerjaData() {
        try {
            const rencana = await this.sheets.getSheet("RENCANA KP BULANAN");
            const realisasi = await this.sheets.getSheet("REALISASI KP BULANAN 2025");

            if (!rencana || !realisasi) {
                console.error("Sheet tidak ditemukan atau gagal dimuat.");
                return null;
            }

            const TARIF_LIST = [
                "JUMLAH",
                "Jumlah Sosial (S)",
                "S-2 / 450 VA", "S-2 / 900 VA", "S-2 / 1300 VA", "S-2 / 2200 VA",
                "S-2 / > 2200 VA s.d 200 kVA", "S-3 / > 200 kVA",
                "Jumlah Rumah Tangga (R)",
                "R-1 / 450 VA", "R-1 / 900 VA", "R-1 / 1300 VA", "R-1 / 2200 VA",
                "R-2 / > 2200 s.d 6600 VA", "R-3 / > 6600 VA",
                "Jumlah Bisnis (B)",
                "B-1 / 450 VA", "B-1 / 900 VA", "B-1 / 1300 VA", "B-1 / 2200 VA",
                "B-2 / > 2200 VA s.d 200 kVA", "B-3 / > 200 kVA",
                "Jumlah Industri (I)",
                "I-1 / 450 VA", "I-1 / 900 VA", "I-1 / 1300 VA", "I-1 / 2200 VA",
                "I-1/2200 s/d 14000 VA", "I-2/>14 s/d 200 kVA",
                "I-3/>200 kVA s/d 5.000 kVA", "I-3/>5.000 kVA s/d 30.000 kVA", "I-4/>30.000 kVA",
                "Jumlah Publik (P)",
                "P-1 / 450 VA", "P-1 / 900 VA", "P-1 / 1300 VA", "P-1 / 2200 VA",
                "P-1 / > 2200 VA s.d 200 kVA", "P-2 / > 200 kVA", "P3",
                "Jumlah Multiguna (M)",
                "M / PS (Penerangan Sementara)", "M / TS1 (Non Pelanggan)",
                "MB/ Multi Guna Prabayar", "T > 200 kVA", "C > 200 kVA"
            ];

            // Ambil data semua tarif
            const perTarif = {};
            TARIF_LIST.forEach(tarif => {
                const keyword = tarif.toUpperCase() === "JUMLAH" ? "JUMLAH" : tarif;
                const rRow = rencana.find(row =>
                    row[0] && row[0].toString().trim() === keyword
                );
                const rlRow = realisasi.find(row =>
                    row[0] && row[0].toString().trim() === keyword
                );
                perTarif[tarif] = {
                    target: rRow ? rRow.slice(1, 13).map(v => this.parseNumber(v)) : new Array(12).fill(0),
                    realisasi: rlRow ? rlRow.slice(1, 13).map(v => this.parseNumber(v)) : new Array(12).fill(0)
                };
            });

            const targetBulanan = perTarif["JUMLAH"].target;
            const realisasiBulanan = perTarif["JUMLAH"].realisasi;

            let accT = 0, accR = 0;
            const targetAcc = targetBulanan.map(v => accT += v);
            const realAcc = realisasiBulanan.map(v => accR += v);

            return {
                bulanan: { target: targetBulanan, realisasi: realisasiBulanan },
                persenBulanan: realisasiBulanan.map((v, i) =>
                    targetBulanan[i] ? (v / targetBulanan[i] * 100) : 0
                ),
                akumulasi: { target: targetAcc, realisasi: realAcc },
                persenAkumulasi: realAcc.map((v, i) =>
                    targetAcc[i] ? (v / targetAcc[i] * 100) : 0
                ),
                perTarif,
                tarifList: TARIF_LIST
            };
        } catch (err) {
            console.error("Gagal Load Penjualan:", err);
            return null;
        }
    }

    parseNumber(value) {
        if (typeof value === 'number') return value;
        if (!value || value === "-" || value === "") return 0;
        const clean = value.toString()
            .replace(/\./g, '')
            .replace(',', '.');
        return parseFloat(clean) || 0;
    }
}