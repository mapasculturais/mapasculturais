app.component('event-info', {
    template: $TEMPLATES['event-info'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('event-info')
        return { text }
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
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },

    mounted() {
        this.descricaoSonora = "";
        this.traducaoLibras = "";
    },

    methods: {
        accessibilityResources() {
            if (this.entity.acessibilidade_fisica) {
                return this.entity.acessibilidade_fisica.split(';');
            }
        }
    }
});
