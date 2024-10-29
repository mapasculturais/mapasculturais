app.component('registration-edition', {
    template: $TEMPLATES['registration-edition'],

    props: {
        entity: {
            type: Entity,
            required: true,
        }
    },

    setup() {
        const text = Utils.getTexts('entity');
        return { text }
    },
})
