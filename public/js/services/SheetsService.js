class SheetsService {

    constructor(url){
        this.url = url;
        this.cache = null;
    }

    async fetchData(){
        if(this.cache) return this.cache;

        const response = await fetch(this.url);

        if(!response.ok){
            throw new Error("Gagal mengambil data dari Sheets");
        }

        this.cache = await response.json();
        return this.cache;
    }

    async getSheet(sheetName){
        const data = await this.fetchData();
        return data[sheetName] || [];
    }

    findRow(sheet, keyword){
        return sheet.find(row => row[0] === keyword);
    }

    extractMonths(row, start=6, total=6){
        return row.slice(start, start + total)
                  .map(v => parseFloat(v) || 0);
    }
}

export default SheetsService;