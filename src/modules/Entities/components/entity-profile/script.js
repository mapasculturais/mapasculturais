app.component('entity-profile', {
    template: $TEMPLATES['entity-profile'],

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },
});
