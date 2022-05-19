app.component('entity-header', {
    template: $TEMPLATES['entity-header'],
    methods: {
        url (source) {
            return `url(${source})`
        },
    },
})
