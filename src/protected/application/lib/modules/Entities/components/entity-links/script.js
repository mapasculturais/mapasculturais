app.component('entity-links', {
    template: $TEMPLATES['entity-links'],
    emits: [],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-links')
        return { text }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        title: {
            type: String,
            default: 'Links'
        },
        editable: {
            type: Boolean,
            default: false
        },
        classes: {
            type: String,
            default: '',
        },
    },

    data() {
        return {
            metalist: {}
        }
    },

    methods: {
        create() {
            return this.entity.createMetalist('links', this.metalist);      
        },

        save(metalist) {
            metalist.title = metalist.newData.title;
            metalist.value = metalist.newData.value;
            
            return metalist.save();
        }
    }
    
});
