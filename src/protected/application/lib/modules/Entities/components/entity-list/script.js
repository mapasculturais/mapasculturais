app.component('entity-list', {
    template: $TEMPLATES['entity-list'],
    emits: [],
    
    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-list')
        return { text }
    },

    data() {        
        return {
            query: {
                'id': `IN(${this.ids})`,
            },
        };
    },

    props: {    
        title: {
            type: String,
            required: true,
        },
        type: {
            type: String,
            required: true,
        },
        ids:{
            type: Array,
            required: true,
        }
    },    
});
