app.component('search-filter-space', {
    template: $TEMPLATES['search-filter-space'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('search-filter-space')
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
            types: $DESCRIPTIONS.space.type.options,
            statesAndCitiesEnable: !!$MAPAS.config.statesAndCitiesEnable,
            searchTerritorialFilters: $MAPAS.config.searchTerritorialFilters || { statesForced: [], citiesForced: [], showStateFilter: true, showCityFilter: true },
        }
    },

    computed: {
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
        },
    },
});
