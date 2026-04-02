class SheetsService {

    constructor(url){
        this.url = url;
        this.cache = null;
    }

 async fetchData(){
    if(this.cache) return this.cache;

    const response = await fetch(this.url);
    const text = await response.text();
    
    try {
        this.cache = JSON.parse(text);
    } catch(e) {
        console.error("Gagal parse JSON:", e);
        console.log("Raw response:", text.substring(0, 300));
        return {};
    }
    
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