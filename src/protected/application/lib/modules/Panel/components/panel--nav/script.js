app.component('panel--nav', {
    template: $TEMPLATES['panel--nav'],

    props: {
        classes: {
            type: [Array, String],
            default: ''
        }
    },

    data() {
        const global = useGlobalState();
        return {
            entity: global.auth.user?.profile,
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
