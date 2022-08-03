app.component('entity-card', {
    template: $TEMPLATES['entity-card'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-card')
        return { text }
    },

    mounted() {
    },

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },

    data() {
        return {
        }
    },

    computed: {
    },
    
    methods: {
    },
});
