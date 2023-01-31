app.component('opportunity-subscription-content' , {
    template: $TEMPLATES['opportunity-subscription-content'],

    data () {
        return {}
    },

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },

    computed: {
        isLogged () {
            return $MAPAS.userId != null
        }
    }
});