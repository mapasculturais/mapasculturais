
app.component('mc-map-card', {
    template: $TEMPLATES['mc-map-card'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-map-card')
        return { text }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },

    data() {
        return {}
    },

    computed: {},
    
    methods: {},
});
