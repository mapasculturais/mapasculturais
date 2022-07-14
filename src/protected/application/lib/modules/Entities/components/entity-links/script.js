app.component('entity-links', {
    template: $TEMPLATES['entity-links'],
    emits: [],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('__template__')
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
        }

    },
    methods: {
        success() {
            const messages = useMessages();
            messages.success("mensagem de sucesso", 10000);
        },
        error() {
            const messages = useMessages();
            messages.error("Mensagem de erro",10000)
        },
        warning() {
            const messages = useMessages();
            messages.warning("Mensagem de aviso", 10000);
        },
    }
    
});
