app.component('entity-terms', {
    template: $TEMPLATES['entity-terms'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    props: {
        editable: {
            type: Boolean,
            default: false
        },
        entity: {
            type: Entity,
            required: true
        },
        taxonomy: {
            type: String,
            required: true
        },
        title: {
            type: String,
            default: ''
        }
    }
});
