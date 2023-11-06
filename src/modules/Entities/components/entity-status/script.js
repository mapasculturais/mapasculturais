app.component('entity-status', {
    template: $TEMPLATES['entity-status'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-status')
        return { text }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },
});
