app.component('panel--entity-card', {
    template: $TEMPLATES['panel--entity-card'],

    props: {
        entity: {
            type: Object,
            required: true
        }
    },
})
