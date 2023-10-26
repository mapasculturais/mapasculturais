app.component('mc-container', {
    template: $TEMPLATES['mc-container'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    }
});
