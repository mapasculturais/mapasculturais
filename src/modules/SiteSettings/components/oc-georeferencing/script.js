app.component('oc-georeferencing', {
    template: $TEMPLATES['oc-georeferencing'],

    components: {
        LMap: VueLeaflet.LMap,
        LTileLayer: VueLeaflet.LTileLayer,
    },

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },
    computed: {
        options() {
            return [
                { label: 'Distrito', value: 'distrito' },
                { label: 'Estado', value: 'estado' },
                { label: 'Mesorregião', value: 'mesorregiao' },
                { label: 'Microrregião', value: 'microrregiao' },
                { label: 'Município', value: 'municipio' },
                { label: 'País', value: 'pais' },
                { label: 'Região', value: 'regiao' },
                { label: 'Setor Censitario', value: 'setor_censitario' },
                { label: 'Subprefeitura', value: 'subprefeitura' },
                { label: 'Zona', value: 'zona' }
            ]
        },
        geoDivisionsFiltersList() {
            return [
                { value: "AC", label: "Acre" },
                { value: "AL", label: "Alagoas" },
                { value: "AM", label: "Amazonas" },
                { value: "AP", label: "Amapá" },
                { value: "BA", label: "Bahia" },
                { value: "CE", label: "Ceará" },
                { value: "DF", label: "Distrito Federal" },
                { value: "ES", label: "Espírito Santo" },
                { value: "GO", label: "Goiás" },
                { value: "MA", label: "Maranhão" },
                { value: "MG", label: "Minas Gerais" },
                { value: "MS", label: "Mato Grosso do Sul" },
                { value: "MT", label: "Mato Grosso" },
                { value: "PA", label: "Pará" },
                { value: "PB", label: "Paraíba" },
                { value: "PE", label: "Pernambuco" },
                { value: "PI", label: "Piauí" },
                { value: "PR", label: "Paraná" },
                { value: "RJ", label: "Rio de Janeiro" },
                { value: "RN", label: "Rio Grande do Norte" },
                { value: "RS", label: "Rio Grande do Sul" },
                { value: "RO", label: "Rondônia" },
                { value: "RR", label: "Roraima" },
                { value: "SC", label: "Santa Catarina" },
                { value: "SE", label: "Sergipe" },
                { value: "SP", label: "São Paulo" },
                { value: "TO", label: "Tocantins" }
            ]
        },
        zoom() {
            return this.$refs.map.leafletObject._zoom;
        }
    },
    data() {
        return {
            geodivisions: this.entity.geodivisions || [],
            geoDivisionsFilters: this.entity.geoDivisionsFilters || [],
            selectedItem: "",
            tileServer: $MAPAS.config.map.tileServer,
            filter: '',
            filterResult: [],
            isLoading: false,
            defaultZomm: this.entity.zoom_default ? parseInt(this.entity.zoom_default) : 5,
            minZoom: this.entity.zoom_min ? parseInt(this.entity.zoom_min) : 0,
            maxZoom: this.entity.zoom_max ? parseInt(this.entity.zoom_max) : 22,
            centerMap: {
                latitude: this.entity.latitude || 0,
                longitude: this.entity.longitude || 0,
                lat: this.entity.latitude || 0,
                lng: this.entity.longitude || 0,
            },
        }
    },
    methods: {
        change(key) {
            this.entity.geodivisions = this.geodivisions;
        },
        changeFilters(key) {
            this.entity.geoDivisionsFilters = this.geoDivisionsFilters;
        },
      
        setDefaultZoom() {
            let zoom = this.$refs.map.leafletObject._zoom;
            this.entity.zoom_default = zoom;
            this.defaultZomm = zoom;
        },

        setMaxZoom() {
            let zoom = this.$refs.map.leafletObject._zoom;
            this.entity.zoom_max = zoom;
            this.maxZoom = zoom;
        },

        setMinZoom() {
            let zoom = this.$refs.map.leafletObject._zoom;
            this.entity.zoom_min = zoom;
            this.minZoom = zoom;
            this.setDefaultZoom();
        },

        setLocation(location) {
            this.centerMap.latitude = location.lat;
            this.centerMap.longitude = location.lon;

            this.entity.latitude = location.lat;
            this.entity.longitude = location.lon;
            this.filterResult = [];
            this.filter = "";
        },

        searchResult() {
            return this.filterResult;
        },

        search() {
            this.isLoading = true;
            let params = {
                q: this.filter,
                format: "json",
                countrycodes: "br",
                addressdetails: 1,
            };

            const url = 'https://nominatim.openstreetmap.org/search' + this.formatParams(params);

            fetch(url)
                .then(response => response.json())
                .then(response => {
                    this.isLoading = false;
                    this.filterResult = response.filter((location) => location.addresstype == 'state' || location.addresstype == 'municipality');
                });
        },
        formatParams(params) {
            return "?" + Object.keys(params).map(function (key) {
                return key + "=" + encodeURIComponent(params[key])
            }).join("&");
        },
        resetZoom() {
            this.entity.zoom_default = 5,
                this.entity.zoom_min = 0,
                this.entity.zoom_max = 22,
                this.minZoom = 0;
            this.maxZoom = 22;
            this.defaultZomm = 5;
            this.entity.save();
        }
    }
});
