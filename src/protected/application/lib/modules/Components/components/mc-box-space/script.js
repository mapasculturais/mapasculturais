app.component('mc-box-space', {
    template: $TEMPLATES['mc-box-space'],
    emits: [],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-box-space')
        return { text }
    },
    
    data() {
        return {
            
        };
    },

    props: {
        
        entity: {
            type: Entity,
            required: true
        },

        editable: {
            type: Boolean,
            default: false
        },
    },

    computed: {
        
    },

    methods: {
        accessibilityResources() {
            if(this.entity.acessibilidade_fisica){
                return this.entity.acessibilidade_fisica.split(';');
            }
        }

    }
});
