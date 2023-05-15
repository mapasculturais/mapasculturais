app.component('agent-data', {
    template: $TEMPLATES['agent-data'],

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
        title: {
            type: String,
            default: __('Dados Pessoais', 'agent-data')
        },

        classes: {
            type: [String, Array, Object],
            required: false
        },
        secondTitle: {
            type: String,
            default: __('Dados Pessoais Sensíveis', 'agent-data')
        }
    },

    data() {
        return {

        }
    },

    mounted() {

    },

    computed: {

    },

    methods: {
    }

});
