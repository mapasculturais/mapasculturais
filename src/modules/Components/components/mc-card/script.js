app.component('mc-card', {
    template: $TEMPLATES['mc-card'],
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
        classes: {
            type: [String, Array, Object],
            required: false
        },
    }
});
