app.component('seal-form-information-seal', {
    template: $TEMPLATES['seal-form-information-seal'],
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
    setup() {
        const text = Utils.getTexts('seal-form-information-seal');
        return { text };
    }
});