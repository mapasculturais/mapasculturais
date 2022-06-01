app.component('entity-seals', {
    template: $TEMPLATES['entity-seals'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        title: {
            type: String,
            default: 'Verificações'
        },
        editable: {
            type: Boolean,
            default: false
        }
    },
});
