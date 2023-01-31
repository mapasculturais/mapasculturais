app.component('opportunity-subscription-logged-out' , {
    template: $TEMPLATES['opportunity-subscription-logged-out'],

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