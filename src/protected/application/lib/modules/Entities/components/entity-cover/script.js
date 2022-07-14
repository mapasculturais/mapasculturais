app.component('entity-cover', {
    template: $TEMPLATES['entity-cover'],
    emits: [],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        name: {
            type: String,
            default: ''
        }
    },
});
