app.component('mc-map', {
    template: $TEMPLATES['mc-map'],

    // define os eventos que este componente emite
    emits: ['ready'],

    components: {
        LMap: VueLeaflet.LMap,
        LTileLayer: VueLeaflet.LTileLayer,
        LControlLayers: VueLeaflet.LControlLayers,
        // LIcon: VueLeaflet.LIcon,
        // LMarker: VueLeaflet.LMarker,
        // LTooltip: VueLeaflet.LTooltip,
        // LPopup: VueLeaflet.LPopup,
        // LPolyline: VueLeaflet.LPolyline,
        // LPolygon: VueLeaflet.LPolygon,
        // LRectangle: VueLeaflet.LRectangle,
    },

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-map')
        return {
            text
        }
    },
    
    props: {
        center: {
            type: Object,
            default: $MAPAS.config.map.center
        }
    },

    data() {
        return {
            tileServer: $MAPAS.config.map.tileServer,
            defaultZoom: $MAPAS.config.map.defaultZoom,
            approximateZoom: $MAPAS.config.map.approximateZoom,
            preciseZoom: $MAPAS.config.map.preciseZoom,
            maxZoom: $MAPAS.config.map.maxZoom,
            minZoom: $MAPAS.config.map.minZoom,
        };
    },

    computed: {},

    methods: {
        async handleMapSetup () {
            const leafletObject = Vue.toRaw(this.$refs.map.leafletObject);
            leafletObject.markersGroup = L.markerClusterGroup({
                maxClusterRadius: 35
            });
            leafletObject.addLayer(leafletObject.markersGroup);
            this.$emit('ready', leafletObject);
        },
    },
});