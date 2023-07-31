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
    methods: {
        showContent(name){
            if(name.length > 45){
                return name.substring(0,45)+'...';
            } else {
                return name
            }
        },
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
