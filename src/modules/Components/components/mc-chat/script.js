app.component('mc-chat', {
    template: $TEMPLATES['mc-chat'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },
    props: {
        tag: {
            type: String,
            default: 'article'
        },
        entity: {
            type: Entity,
            required: true
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },
    mounted() {
    }
});
