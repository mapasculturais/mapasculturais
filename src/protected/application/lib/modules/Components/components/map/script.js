class MapPoint {
    constructor(entity) {
        this.entity = entity;
        this.entityType = entity.__objectType;
        this.entityName = entity.name;
        this.latitude = entity.location.latitude;
        this.longitude = entity.location.longitude;
    }
}

app.component('map', {
    template: $TEMPLATES['map'],
    
    // define os eventos que este componente emite
    emits: [],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('map')
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
            required: false
        },

        points: {
            type: Array,
            required: false,
            validator: prop => Array.isArray(prop) && prop.every(item => item instanceof MapPoint)
        },
    },

    data() {
        return {
        }
    },

    computed: {
    },
    
    methods: {
    },
});
