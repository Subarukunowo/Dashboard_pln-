export default class BppChart{

constructor(canvasId){
this.canvasId = canvasId
this.chart = null
}

render(konsolidasi, internal, labels = null){

const ctx = document.getElementById(this.canvasId)

if(this.chart){
this.chart.destroy()
}

const defaultLabels = [
"Jan","Feb","Mar","Apr","Mei","Jun",
"Jul","Agu","Sep","Okt","Nov","Des"
]

this.chart = new Chart(ctx,{

type:'bar',

data:{
labels: labels ?? defaultLabels,

datasets:[
{
label:'Versi Konsolidasi',
data:konsolidasi,
backgroundColor:'#ff9800'
},
{
label:'Versi Internal',
data:internal,
backgroundColor:'#00ff88'
}
]
},

options:{
responsive:true,
maintainAspectRatio:false
}

})

}

}