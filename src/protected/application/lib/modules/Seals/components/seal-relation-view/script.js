app.component('seal-relation-view', {
    template: $TEMPLATES['seal-relation-view'],
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
    data () {
        return {}
    }
});