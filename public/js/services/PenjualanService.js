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

            const rowRencana = rencana.find(row =>
                row[0] && row[0].toString().trim().toUpperCase() === "JUMLAH"
            );
            const rowRealisasi = realisasi.find(row =>
                row[0] && row[0].toString().trim().toUpperCase() === "JUMLAH"
            );

            if (!rowRencana || !rowRealisasi) {
                console.error("Baris JUMLAH tidak ditemukan!");
                return null;
            }

            const targetBulanan = rowRencana.slice(1, 13).map(v => this.parseNumber(v));
            const realisasiBulanan = rowRealisasi.slice(1, 13).map(v => this.parseNumber(v));

            return {
                bulanan: {
                    target: targetBulanan,
                    realisasi: realisasiBulanan
                },
                persenBulanan: realisasiBulanan.map((v, i) =>
                    targetBulanan[i] ? (v / targetBulanan[i] * 100) : 0
                )
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