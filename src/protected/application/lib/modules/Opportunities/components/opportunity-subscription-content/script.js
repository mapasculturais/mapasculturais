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
        },
        dateStart () {
            return new Date($MAPAS.requestedEntity.registrationFrom.date).toLocaleDateString();
        },
        dateEnd () {
            return new Date($MAPAS.requestedEntity.registrationTo.date).toLocaleString();
        }
    }
});