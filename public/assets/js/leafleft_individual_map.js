
class LeafleftMap {

    constructor(options = {}) {
        this.options = {
            id : options.id,
            date: options.date,            
            fullscreenControl: options.fullscreen || true,
            zoom: options.zoom || 4,
        };
 
        let map = this.initMap();
        this.getMarkers(map);    
        
    }


    initMap() {
        // calculer le centre de la positiion d'un ensemble de point
        var lat = 47.555; //temporaire
        var lon = 1.25854; //temporaire
        var map = L.map('map').setView([lat, lon], this.options.zoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {}).addTo(map);
 
        return map;    
    }

   
    getVector(data, map) {
        var vector = L.polyline(data, {color: 'black', stroke: '1'}).arrowheads({frequency: '50px', size: '12px'});
        vector.addTo(map);
    }
 
    getMarkers(map) {
        const points = [];

        $.get("/api/user/locate/" + this.options.id , (data, status) => {
            
            if (!Array.isArray(data))
                return;

            data.reverse(); // du plus ancien au plus recent
            
            data.forEach(element => {

                 console.log(element);
                 points.push([element.lat, element.lon]);
    
                   var icon = L.icon({
                       iconUrl: "../assets/img/target.png",
                       iconSize: [30, 30],
                   });
                   L.marker([element.lat, element.lon], {icon: icon}).addTo(map).bindPopup(element.created);
                 
             });
             this.getVector(points, map);
        });      
    }
}