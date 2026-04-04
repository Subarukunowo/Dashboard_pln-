export default class BppService {

    constructor(sheetsService){
        this.sheets = sheetsService
    }

    extractMonths(row){

        if(!row){
            return new Array(12).fill(0)
        }

        const months = []

        for(let i=4;i<=15;i++){

            const val = parseFloat(row[i])

            months.push(isNaN(val) ? 0 : val)

        }

        return months
    }

    async getAllBpp(){

        const sheet = await this.sheets.getSheet("BPP")

        if(!Array.isArray(sheet)){
            throw new Error("Data BPP tidak valid")
        }

        const types = [
            "BPP PEMBANGKIT",
            "BPP S.D TRANSMISI",
            "BPP S.D DIDTRIBUSI TM",
            "BPP S.D DIDTRIBUSI TR"
        ]

        const result = {}

        types.forEach(type => {

            const rows = sheet.filter(r => r && r[3] === type)

            if(rows.length >= 2){

                result[type] = {
                    konsolidasi: this.extractMonths(rows[0]),
                    internal: this.extractMonths(rows[1])
                }

            }

        })

        return result
    }

}