app.component('entity-map', {
    template: $TEMPLATES['entity-map'],
    
    // define os eventos que este componente emite
    emits: ['change'],

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
        const text = Utils.getTexts('entity-map')
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

        editable: {
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
        change ($event) {
            this.entity.location = {...$event.target._latlng};

            this.$emit('change', this.entity);
        }
    },
});
