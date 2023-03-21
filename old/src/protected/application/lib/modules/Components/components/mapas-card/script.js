app.component('mapas-card', {
    template: $TEMPLATES['mapas-card'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },
    props: {
        noTitle: {
            type: Boolean,
            default: false
        },
    }
});
