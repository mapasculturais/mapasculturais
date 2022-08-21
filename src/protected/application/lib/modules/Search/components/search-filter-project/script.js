app.component('search-filter-project', {
    template: $TEMPLATES['search-filter-project'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('search-filter-project')
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
    },
});
