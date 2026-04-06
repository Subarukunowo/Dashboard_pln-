const realisasiSheet = await this.sheets.getSheet("REALISASI ");
const realisasiBulanan = realisasiSheet.slice(3, 15).map(row => ({
    bulan:      row[0],
    target:     this.parseNumber(row[1]),
    realisasi:  this.parseNumber(row[2]),
    delta:      this.parseNumber(row[3]),
    pencapaian: this.parseNumber(row[4]),
}));

return { tarif: TARIF, data: result, realisasiBulanan };