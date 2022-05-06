app.component('panel--entity-header', {
    template: $TEMPLATES['panel--entity-header'],
    methods: {
        url (source) {
            return `url(${source})`
        },
    },
})
