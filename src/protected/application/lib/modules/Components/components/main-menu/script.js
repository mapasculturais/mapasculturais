app.component('main-menu', {
    template: $TEMPLATES['main-menu'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },
});
