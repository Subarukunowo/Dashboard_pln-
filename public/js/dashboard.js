// Line Chart

new Chart(document.getElementById("areaChart"),{
type:'line',
data:{
labels:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
datasets:[{
label:"Customer Growth",
data:[380000,390000,395000,400000,402737],
borderColor:"orange",
backgroundColor:"rgba(255,165,0,0.2)"
}]
}
});

// Pie Chart Area

new Chart(document.getElementById("pie1"),{
type:'pie',
data:{
labels:["Batam Center","Batu Aji","Tiban","Nagoya"],
datasets:[{
data:[32,25,22,21],
backgroundColor:["orange","yellow","green","blue"]
}]
}
});

// Pie Chart Rate

new Chart(document.getElementById("pie2"),{
type:'pie',
data:{
labels:["Rumah Tangga","Bisnis","Sosial","Industri"],
datasets:[{
data:[83,10,5,2],
backgroundColor:["orange","yellow","green","blue"]
}]
}
});
function updateClock(){
let now=new Date();
document.getElementById("clock").innerHTML=
now.toLocaleDateString()+" "+now.toLocaleTimeString();
}

setInterval(updateClock,1000);