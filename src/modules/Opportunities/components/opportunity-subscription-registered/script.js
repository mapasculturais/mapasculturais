app.component('opportunity-subscription-registered' , {
    template: $TEMPLATES['opportunity-subscription-registered'],

    data () {
        return {}
    },

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },
});