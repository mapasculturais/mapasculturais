app.component('entity-cover', {
    template: $TEMPLATES['entity-cover'],
    emits: [],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },
});
