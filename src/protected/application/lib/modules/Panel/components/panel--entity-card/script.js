app.component('panel--entity-card', {
    template: $TEMPLATES['panel--entity-card'],

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },
})
