/**
 * Vue Lifecycle
 * 1. setup
 * 2. beforeCreate
 * 3. created
 * 4. beforeMount
 * 5. mounted
 * 
 * // sempre que há modificação nos dados
 *  - beforeUpdate
 *  - updated
 * 
 * 6. beforeUnmount
 * 7. unmounted                  
 */

app.component('mc-map-markercluster', {
    template: $TEMPLATES['mc-map-markercluster'],
    
    // define os eventos que este componente emite
    emits: ['namesDefined'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-map-markercluster')
        return { text }
    },

    beforeCreate() { },
    created() { },

    beforeMount() { },
    mounted() { 
        const leafletObject = Vue.toRaw(this.$parent.leafletObject);
        const entity = Vue.toRaw(this.entity);
        let interval = setInterval(() => {
            if(leafletObject.markersGroup) {
                clearInterval(interval);
                
                if (!entity.location || !entity.location.lat || !entity.location.lng) {
                    return;
                }


                const options = { title: entity.name, clickable: true, draggable: false }
                
                const marker = L.marker(entity.location, options);
                leafletObject.markers = leafletObject.markers || [];
                // leafletObject.markersGroup.addLayer(marker);
                
                
                leafletObject.markers.push(marker);
                clearTimeout(leafletObject.addLayersTimeout);
                
                leafletObject.addLayersTimeout = setTimeout(() => {
                    leafletObject.markersGroup.addLayers(leafletObject.markers);
                    leafletObject.markers = [];

                },100);
            }
        },10);
    },

    beforeUpdate() { },
    updated() { },

    beforeUnmount() {},
    unmounted() {},

    props: {
        entity: {
            type: Entity,
            required: true
        },

        map: null
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
