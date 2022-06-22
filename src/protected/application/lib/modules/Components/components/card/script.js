app.component('card', {
    template: $TEMPLATES['card'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    }
});
