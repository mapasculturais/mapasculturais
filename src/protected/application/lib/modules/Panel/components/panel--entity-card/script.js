app.component('panel--entity-card', {
    template: $TEMPLATES['panel--entity-card'],
    emits: ['deleted', 'destroyed', 'published', 'archived'],

    props: {
        entity: {
            type: Entity,
            required: true
        },

        onDeleteRemoveFromLists: {
            type: Boolean,
            default: true
        }
    },
})
