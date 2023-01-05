app.component('form-information-seal', {
    template: $TEMPLATES['form-information-seal'],
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
        const text = Utils.getTexts('form-information-seal')
        return { text }
    }
});