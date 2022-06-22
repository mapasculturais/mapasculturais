app.component('container', {
    template: $TEMPLATES['container'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    }
});
