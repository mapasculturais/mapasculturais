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
        }

    },
    methods: {
        addLink(title, link) {
            // @todo tratamento de erros e adição do novo link

            // limpar campos
            document.querySelector("input[name='newLinkTitle']").value="";
            document.querySelector("input[name='newLink']").value="";
        },
        success() {
            const messages = useMessages();
            messages.success("Isso é um snackbar de confirmação", 10000);
        },
        error() {
            const messages = useMessages();
            messages.error("Isso é um snackbar de erro",10000)
        },
        warning() {
            const messages = useMessages();
            messages.warning("Isso é um snackbar de aviso", 10000);
        },
    }
    
});
