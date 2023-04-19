app.component('registration-evaluation-summary', {
    template: $TEMPLATES['registration-evaluation-summary'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('registration-evaluation-summary');
        return { text }
    },

    async created() {
    },

    data() {
        return {
            open: false
        }
    },

    computed: {
    },
    
    methods: {
        toggle () {
            this.open = !this.open;
        }
    },
});
