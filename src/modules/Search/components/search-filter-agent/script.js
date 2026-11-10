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

    beforeCreate() {
        this.pseudoQuery['En_Estado'] = this.pseudoQuery['En_Estado'] || [];
        this.pseudoQuery['En_Municipio'] = this.pseudoQuery['En_Municipio'] || [];
    },

    data() {
        return {
            terms: $TAXONOMIES.area.terms,
            statesAndCitiesEnable: !!$MAPAS.config.statesAndCitiesEnable,
            searchTerritorialFilters: $MAPAS.config.searchTerritorialFilters || { statesForced: [], citiesForced: [], showStateFilter: true, showCityFilter: true },
        }
    },

    methods: {
        clearFilters() {
            const types = ['string', 'boolean'];
            for (const key in this.pseudoQuery) {
                if (Array.isArray(this.pseudoQuery[key])) {
                    this.pseudoQuery[key] = [];
                } else if (types.includes(typeof this.pseudoQuery[key])) {
                    delete this.pseudoQuery[key];
                }
            }
        }
    },
});
