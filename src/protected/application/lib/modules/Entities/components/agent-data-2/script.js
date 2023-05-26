app.component('agent-data-2', {
    template: $TEMPLATES['agent-data-2'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-owner')
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
        },
      
    },
    methods: {
        verifyEntity() {
            let empty = true;
            for (let fieldName of $MAPAS.config['agent-data-2']) {
                let field = this.entity[fieldName]
                if (field !== undefined && field !== null) {
                    if (field instanceof Array) {
                        if (field.length) {
                            empty = false;
                        }
                    }
                    else {
                        empty = false;
                    }
                }
            }
            return !empty;
        },

    },
});
