app.component('search-filter-event', {
    template: $TEMPLATES['search-filter-event'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('search-filter-event')
        return { text }
    },

    props: {
        position: {
            type: String,
            default: 'list'
        },
        pseudoQuery: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            terms: $TAXONOMIES.linguagem.terms,
        }
    },

    computed: {
    },
    
    methods: {
    },
});
