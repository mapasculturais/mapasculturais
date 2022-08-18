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
        api: {
            type: API,
            required: true
        },
        query: {
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
    },
});
