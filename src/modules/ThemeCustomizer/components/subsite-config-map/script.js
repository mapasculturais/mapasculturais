app.component('subsite-config-map', {
    template: $TEMPLATES['subsite-config-map'],

    components: {
        LMap: VueLeaflet.LMap,
        LTileLayer: VueLeaflet.LTileLayer,
    },

    data() {
        let subsite = $MAPAS.subsite;
        return {
            subsite,
            tileServer: $MAPAS.config.map.tileServer,
            filter: '',
            filterResult: [],
        }
    },

    computed: {
        zoom() {
            return this.$refs.map.leafletObject._zoom;
        },

        searchResult() {
            return this.filterResult;
        },

        centerMap() {
            return {
                latitude: this.subsite.latitude ?? 0,
                longitude: this.subsite.longitude ?? 0,
                lat: this.subsite.latitude ?? 0,
                lng: this.subsite.longitude ?? 0,
            }
        }
    },

    methods: {
        setDefaultZoom() {
            console.log(this.zoom);
            this.subsite.zoom_default = this.zoom;
            this.subsite.save();
        },

        setMaxZoom() {
            this.subsite.zoom_max = this.zoom;
            this.subsite.save();
        },

        setMinZoom() {
            this.subsite.zoom_min = this.zoom;
            this.subsite.save();
        },

        setLocation(location) {
            console.log(location);
            this.subsite.latitude = location.lat;
            this.subsite.longitude = location.lon;
            this.subsite.save();
        },

        search() {
            let params = {
                q: this.filter,
                format: "json",
                countrycodes: "br",
                addressdetails: 1,
            };
            
            const url = 'https://nominatim.openstreetmap.org/search' + this.formatParams(params);

            fetch(url)
                .then( response => response.json() )
                .then( response => {
                    this.filterResult = response.filter((location) => location.addresstype == 'state' || location.addresstype == 'municipality');
                });
        },

        formatParams( params ){
            return "?" + Object.keys(params).map(function(key){
                            return key+"="+encodeURIComponent(params[key])
                        }).join("&");
        },
    },
});
