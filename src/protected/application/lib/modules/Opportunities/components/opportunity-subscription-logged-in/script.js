app.component('opportunity-subscription-logged-in' , {
    template: $TEMPLATES['opportunity-subscription-logged-in'],

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