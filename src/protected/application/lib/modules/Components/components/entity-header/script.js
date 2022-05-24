app.component('entity-header', {
    template: $TEMPLATES['entity-header'],
    props: {
        entity: {
            type: Entity,
            required: true
        }
    },
    methods: {
        url (source) {
            return `url(${source})`
        },
    },
})
