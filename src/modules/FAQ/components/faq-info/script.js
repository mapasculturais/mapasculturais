app.component('faq-info', {
    template: $TEMPLATES['faq-info'],
    
    // define os eventos que este componente emite
    emits: ['namesDefined'],

    props: {
        path: {
            type: String,
            required: true
        },

        title: {
            type: String,
            required: false
        },
    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('faq-info')
        return { text, hasSlot }
    },

    data() {
        return {
            question: $MAPAS.config['faq-info'][this.path]
        }
    },

    computed: {
        tags() {
            return this.question.tags || [];
        },

        answer () {
            return this.question.answer;
        }
    },
});
