app.component('entity-log', {
    template: $TEMPLATES['entity-log'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-log')
        return { text }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },

        classes: {
            type: [String, Array, Object],
            required: false
        }
    }
});
