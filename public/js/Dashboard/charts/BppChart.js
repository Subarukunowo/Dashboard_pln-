export default class BppChart{

constructor(canvasId){
this.canvasId = canvasId
this.chart = null
}

render(konsolidasi, internal, labels, mode = "trend"){
    
    const ctx = document.getElementById(this.canvasId)

    if(this.chart){
        this.chart.destroy()
    }

    const isSingle = mode === "single"

    this.chart = new Chart(ctx,{
        type: isSingle ? 'bar' : 'line',

        data:{
            labels,
            datasets:[
                {
                    label:'Konsolidasi',
                    data:konsolidasi,
                    borderColor:'#ff9800',
                    backgroundColor: isSingle ? '#ff9800' : 'transparent',
                    tension:0.3
                },
                {
                    label:'Internal',
                    data:internal,
                    borderColor:'#00ff88',
                    backgroundColor: isSingle ? '#00ff88' : 'transparent',
                    tension:0.3
                }
            ]
        }
    })
}

}