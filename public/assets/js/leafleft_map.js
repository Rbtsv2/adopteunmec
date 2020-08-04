
class LeafleftMap {

    constructor(options = {}) {
        this.options = {
            layers: options.layers || [{name: 'Homme'},{name: 'Femme'}],                 
            fullscreenControl: options.fullscreen || true,
            zoom: options.zoom || 14,
         
        
        };
 
        this.map = null;

        // 1er fonction utiliser une promese tant que la focntion ne renvoit pas la position on n'execite pas les fonctions
        this.getUserPosition().then(position => {

            let layers = this.constructLayers(); // On construct les layers
            
            let markers = this.constructMap(position, layers); // On initialise la carte et on recupere les marqueurs
         
            this.addLayersControls(layers); // On ajoute les layers au controle pour la vue 

            this.addCirclePosition(position); // On ajoute la zone en focntion de la position

            this.getApiAdresses(layers); // search par adresse et recharge la carte en suivant en recuperant les marqueurs
            this.onMapMove(markers, layers); // L'utilisateur deplace la carte et rafraichi les marqueurs au deplacements

            
            
        }).catch(error => {
            console.log('Can\'t get user position', error);
        })
    }

    constructLayers() {
        let overlay = []; // correction ce n'etait pas un object mais un tableau renvoyé

        this.options.layers.forEach(layer => {
            overlay[layer.name] = L.layerGroup();
        })

        return overlay;
    }

    constructMap(position, layers) {
        this.map = L.map('map', {
            fullscreenControl: this.options.fullscreen,
            center: L.latLng( position.coords.latitude,position.coords.longitude),
           
            zoom: this.options.zoom, 
            layers: layers
        });

        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{}).addTo(this.map);
        let markers = this.getMarkers(position.coords.latitude, position.coords.longitude, layers);

       
        return markers;
        
    }

    addCirclePosition(position) {

        var circle = L.circle([position.coords.latitude, position.coords.longitude], 100000, {
            color: 'red',
            fillColor: '#f03',
            fillOpacity: 0.5
        }).addTo(this.map);
    }

    getMarkers(lat, lon, layers) {
        let markers = [];
        let groupMarkers = L.markerClusterGroup();
        $.get("/api/users/" + lat + "/" + lon, (data, status) =>{
            
            if (!Array.isArray(data))
                return;

            data.forEach(element => {
           
                if (element.sexe == true) {
                    let pointer = "/assets/buckets/" +  element.urlid + "/" + element.name;

                    var icon = L.icon({
                        iconUrl: pointer,
                        iconSize: [40, 40],
                        className : 'ico_girl'
                    });

                    var marker = L.marker([element.lat, element.lng], {icon: icon}).addTo(layers['Femme']);
                    markers.push(marker);
                    

                    groupMarkers.addLayer(marker);
                    //controle lies aux markers, affichage du profile sur la gauche  
                    marker.on('click', openProfil);
                    this.map.on('click', deleteProfile);

                    function openProfil(e) {
                        $("#profile").empty();
                        $("#profile").append('<img class="deco_img" src=" assets/buckets/'+  element.urlid + "/" + element.name +' "/><span> '+ element.pseudo + ', âge '+ element.age +' ans <span/> <a class="lien" href= "/product/'+ element.urlid +'" target="_blank"> voir </a>');
                    }

                    function deleteProfile(e) {
                        $("#profile").empty();
                    }
                }
                else {
                    let pointer = "/assets/buckets/" +  element.urlid + "/" + element.name;

                    var icon = L.icon({
                        iconUrl: pointer,
                        iconSize: [40, 40],
                        className : 'ico_man'
                    });

                    var marker = L.marker([element.lat, element.lng], {icon: icon}).addTo(layers['Homme']);
                    markers.push(marker);

                    groupMarkers.addLayer(marker);
                    //controle lies aux markers, affichage du profile sur la gauche  
                    marker.on('click', e => {
                        $("#profile").empty();
                        $("#profile").append('<img class="deco_img" src=" assets/buckets/'+  element.urlid + "/" + element.name +' "/><span> '+ element.pseudo + ', âge '+ element.age +' ans <span/> <a class="lien" href= "/product/'+ element.urlid +'" target="_blank"> voir </a>');
                    });

                    this.map.on('click', e => {
                        $("#profile").empty();
                    });
                
                }

                
            });
            
        });
        this.map.addLayer(groupMarkers);
        return markers;

    }

    addLayersControls(layers) {

        var overlay = {
            "Femmes": layers['Femme'], 
            "Hommes": layers['Homme']
        };
        L.control.layers('', overlay,{collapsed: false} ).addTo(this.map);
        
    }


    getUserPosition() {
        return new Promise((resolve, reject) => {
            navigator.geolocation.watchPosition(position => {
                resolve(position);
            }, error => reject(error));
        })
    }

    onMapMove(markers, layers) { // il faut lui faire passer les noueveaux marqueurs
       
        let center = {};
        this.map.on('moveend', () => {
            center = this.map.getCenter();
            //let zoom = this.map.getZoom();
            // this.removeMarkersOutWindows(layers).then( response => {
            //     var markers = this.getMarkers(center.lat, center.lng, layers)
            // })
               
            this.getMarkers(center.lat, center.lng, layers);

        });
    }

    removeMarkersOutWindows(layers) {

        return new Promise((resolve, reject) => {
            //  markers.forEach( element =>{
            //      // console.log(element);
            //      this.map.removeLayer(element);
            //  }); 
            resolve('success update');
        })
     
    }

    getApiAdresses(layers) {
       
        let address = document.getElementById('address');
        let addresses_list = document.getElementById('addresses_list');

        if (address) {
            address.addEventListener('keyup', (event) =>{
                if (event.keyCode == 13) {
                    $.get('https://nominatim.openstreetmap.org/search?q='+encodeURIComponent(event.target.value)+'&format=json&polygon=1&addressdetails=1', function(addresses) {
                    
                        if (Array.isArray(addresses)) {
                            let html = '';
                            addresses_list.innerHTML = '';

                            addresses.forEach(addr => {
                                let elements = [];

                                if (typeof addr.address.house_number != 'undefined')
                                    elements.push(addr.address.house_number) 
                                if (typeof addr.address.road != 'undefined')
                                    elements.push(addr.address.road);
                                if (typeof addr.address.postcode != 'undefined')
                                    elements.push(addr.address.postcode);
                                if (typeof addr.address.city != 'undefined')
                                    elements.push(addr.address.city);
                                if (typeof addr.address.country != 'undefined')
                                    elements.push(addr.address.country);

                                html += '<div class="address" style="cursor: pointer" data-lat="'+addr.lat+'" data-lon="'+addr.lon+'">' + elements.join(' ') + '</div>';
                            })
                            
                            addresses_list.innerHTML = html;
                            addresses_list.style.display = 'block';

                        }
                    })
                }
      
            });

            $(document).on('click','.address', (event) => {
                this.getMarkers(event.target.dataset.lat, event.target.dataset.lon, layers);
                this.map.flyTo(event.target.dataset);
                addresses_list.style.display = 'none';
            });

        }
    }

    


}