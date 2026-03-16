import SheetsService from "./services/SheetsService.js"
import BppService from "./services/BppService.js"
import BppChart from "./charts/BppChart.js"

const SCRIPT_URL="https://script.google.com/macros/s/AKfycbweFQ59S0HfRkd6gT_D2becqDM9PiFBcnVLSTDiwuJPucFL4Oc3gdGCVY3xfNC1c7uEVg/exec"

class Dashboard{

    constructor(){

        this.sheets = new SheetsService(SCRIPT_URL)

        this.bppService = new BppService(this.sheets)

        this.bppChart = new BppChart("bppComparisonChart")

        this.bppData = null

       // DEFAULT STATE
    this.currentType = "BPP PEMBANGKIT"
    this.currentYear = "2025"
    this.currentMonth = "all"

    }

    async init(){

        try{

            console.log("Loading dashboard...")

            this.bppData = await this.bppService.getAllBpp()

            this.renderChart()

            this.initFilters()

        }
        catch(err){

            console.error("Dashboard Error:",err)

        }

    }

renderChart(){

    const data = this.bppData[this.currentType]

    if(!data) return

    let kons = [...data.konsolidasi]
    let intl = [...data.internal]
    let labels = [
        "Jan","Feb","Mar","Apr","Mei","Jun",
        "Jul","Agu","Sep","Okt","Nov","Des"
    ]

    // FILTER BULAN
    if(this.currentMonth !== "all"){

        const index = parseInt(this.currentMonth)

        kons = [kons[index]]
        intl = [intl[index]]

        labels = [labels[index]]
    }

    this.bppChart.render(kons, intl, labels)

}

initFilters(){

const filterMonth = document.getElementById("filterMonth")
const filterYear = document.getElementById("filterYear")
const filterBpp = document.getElementById("filterBpp")

// FILTER BULAN
filterMonth.addEventListener("change",(e)=>{

    this.currentMonth = e.target.value
    this.renderChart()

})

// FILTER TAHUN
filterYear.addEventListener("change",(e)=>{

    this.currentYear = e.target.value
    this.renderChart()

})

// FILTER JENIS BPP
filterBpp.addEventListener("change",(e)=>{

    this.currentType = e.target.value
    this.renderChart()

})

// SET DEFAULT UI
filterMonth.value = "all"
filterYear.value = "2025"
filterBpp.value = "BPP PEMBANGKIT"

}
}

document.addEventListener("DOMContentLoaded",()=>{

    const dashboard = new Dashboard()

    dashboard.init()

})