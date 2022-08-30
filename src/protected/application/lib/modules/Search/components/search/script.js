app.component('search', {
    template: $TEMPLATES['search'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('search')
        return { text }
    },

    props: {
        pageTitle: {
            type: String,
            required: true
        },
        entityType: {
            type: String,
            required: true
        },
        initialPseudoQuery: {
            type: Object,
            default: null
        }
    },

    data() {
        const pseudoQuery = this.initialPseudoQuery || $MAPAS.initialPseudoQuery || {};
        return { pseudoQuery };
    },

    computed: {
    },
    
    methods: {
    },
});
