app.component('mc-map-marker', {
    template: $TEMPLATES['mc-map-marker'],
    
    // define os eventos que este componente emite
    emits: ['moved'],

    components: {
        // LMap: VueLeaflet.LMap,
        // LTileLayer: VueLeaflet.LTileLayer,
        // LControlLayers: VueLeaflet.LControlLayers,
        LIcon: VueLeaflet.LIcon,
        LMarker: VueLeaflet.LMarker,
        // LTooltip: VueLeaflet.LTooltip,
        // LPopup: VueLeaflet.LPopup,
        // LPolyline: VueLeaflet.LPolyline,
        // LPolygon: VueLeaflet.LPolygon,
        // LRectangle: VueLeaflet.LRectangle,
    },

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-map-marker')
        return { text }
    },

    beforeCreate() { },
    created() { },

    beforeMount() { },
    mounted() { },

    beforeUpdate() { },
    updated() { },

    beforeUnmount() {},
    unmounted() {},

    props: {
        entity: {
            type: Entity,
            required: true
        },

        draggable: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {}
    },

    computed: {
    },
    
    methods: {
        moved ($event) {
            this.$emit('moved', $event);
        }
    },
});
