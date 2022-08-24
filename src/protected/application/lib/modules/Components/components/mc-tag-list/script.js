app.component('mc-tag-list', {
    template: $TEMPLATES['mc-tag-list'],
    emits: [],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-tag-list')
        return { text }
    },
    
    data() {
        return {
            
        };
    },

    props: {
        editable: {
            type: Boolean,
            default: false,
        },
        tags: {
            type: Array,
            default: [],
            required: true,
        },
        entityType: {
            type: String,
            required: true,
        }
    },

    computed: {
        
    },

    methods: {

    }
});
