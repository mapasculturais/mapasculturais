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
        }
    },

    computed: {
        zoom() {
            return this.$refs.map.leafletObject._zoom;
        },
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
    },
});
