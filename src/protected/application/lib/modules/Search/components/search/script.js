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
    },

    data() {
        return {
            pseudoQuery: {},
        }
    },

    computed: {
    },
    
    methods: {
    },
});
