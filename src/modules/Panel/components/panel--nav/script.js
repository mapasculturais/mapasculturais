app.component('panel--nav', {
    template: $TEMPLATES['panel--nav'],

    props: {
        classes: {
            type: [Array, String],
            default: ''
        },
        // entity: {
        //     type: Entity,
        //     required: true
        // }

    },

    data() {
        return {
            entity: $MAPAS.userProfile,
            groups: $MAPAS.config.panelNav

        }
    },

    methods: {
        active(item) {
            const route = $MAPAS.route.route;
            return $MAPAS.activeNav == item.route || route == item.route;
        }
    }
})
