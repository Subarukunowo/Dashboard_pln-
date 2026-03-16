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

this.currentType = "BPP PEMBANGKIT"

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

if(!data){
console.warn("Data tidak ditemukan:",this.currentType)
return
}

let konsolidasi = []
let internal = []
let labels = []

// FULL YEAR
if(this.currentMonth === "all"){

konsolidasi = data.konsolidasi
internal = data.internal

labels = [
"Jan","Feb","Mar","Apr","Mei","Jun",
"Jul","Agu","Sep","Okt","Nov","Des"
]

}

// SINGLE MONTH
else{

const i = parseInt(this.currentMonth)

konsolidasi = [ data.konsolidasi[i] || 0 ]
internal = [ data.internal[i] || 0 ]

labels = [
document
.getElementById("filterMonth")
.selectedOptions[0].text
]

}

this.bppChart.render(konsolidasi,internal,labels)

}

initFilters(){

const filterType = document.getElementById("filterBpp")
const filterMonth = document.getElementById("filterMonth")

if(filterType){

filterType.addEventListener("change",(e)=>{

this.currentType = e.target.value

this.renderChart()

})

}

if(filterMonth){

filterMonth.addEventListener("change",(e)=>{

this.currentMonth = e.target.value

this.renderChart()

})

}

}

}

document.addEventListener("DOMContentLoaded",()=>{

const dashboard = new Dashboard()

dashboard.init()

})