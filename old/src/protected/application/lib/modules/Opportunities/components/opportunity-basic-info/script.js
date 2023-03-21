app.component('opportunity-basic-info' , {
    template: $TEMPLATES['opportunity-basic-info'],

    data () {
        return {
        };
    },

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },
});