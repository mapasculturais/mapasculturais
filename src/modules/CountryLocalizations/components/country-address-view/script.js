
app.component('country-address-view', {
    template: $TEMPLATES['country-address-view'],
    emits: [],

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

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },
    data() {
        return {};
    },
});
