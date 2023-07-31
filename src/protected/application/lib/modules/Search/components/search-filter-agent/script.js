app.component('search-filter-agent', {
    template: $TEMPLATES['search-filter-agent'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('search-filter-agent')
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
            terms: $TAXONOMIES.area.terms,
        }
    },

    computed: {
    },
    
    methods: {
        clearFilters() {
            delete this.pseudoQuery['@verified'];
            delete this.pseudoQuery['type'];
            this.pseudoQuery['term:area'].length = 0;
        }
    },
});
