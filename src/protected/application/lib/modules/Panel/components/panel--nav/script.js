app.component('panel--nav', {
    template: $TEMPLATES['panel--nav'],

    props: {
        classes: {
            type: [Array, String],
            default: ''
        }
    },

    data() {
        return {
            route: $MAPAS.route.route,
            groups: $MAPAS.config.panelNav
        }
    }
})
