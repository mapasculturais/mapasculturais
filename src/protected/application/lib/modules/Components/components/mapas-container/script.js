app.component('mapas-container', {
    template: $TEMPLATES['mapas-container'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    }
});
