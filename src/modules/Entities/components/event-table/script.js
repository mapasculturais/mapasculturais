app.component('event-table', {
    template: $TEMPLATES['event-table'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('event-table')
        return { text }
    },

    props: {
        visibleColumns: {
            type: [Boolean, Array],
            default: [],
        },

        hideFilters: {
            type: Boolean,
            default: false
        },
    },

    data() {
        let additionalHeaders = $MAPAS.config.eventTable.additionalHeaders;
        let _visibleColumns = $MAPAS.config.eventTable.visibleColumns;
        let visible = Object.keys(this.visibleColumns).length > 0 ? this.visibleColumns.join(',') : _visibleColumns.join(',');

        let query = {
            '@select': `owner.{id,name},${visible}`,
            '@order': 'createTimestamp DESC',
            '@limit': 20,
            '@page': 1,
        }

        let getSeals = $MAPAS.config.eventTable.seals;
        let seals = {}
        for (const seal of getSeals) {
            seals[seal.id] = seal.name;
        }

        return {
            additionalHeaders,
            selectedLinguagem: [],
            selectedAgeRating: [],
            selectedSeals: [],
            visible,
            query,
            terms: $TAXONOMIES.linguagem.terms,
            ageRating: $DESCRIPTIONS.event.classificacaoEtaria.optionsOrder,
            seals,
        }
    },

    computed: {
        headers () {
            let itens = [
                { text: __('id', 'event-table'), value: "id", sticky: true, width: '80px'},
                { text: __('name', 'event-table'), value: "name", width: '160px' },
                { text: __('linguagem', 'event-table'), value: "terms.linguagem.join(', ')", slug: "linguagem" },
                { text: __('classificacaoEtaria', 'event-table'), value: "classificacaoEtaria", slug: "classificacaoEtaria" },
                { text: __('tag', 'event-table'), value: "terms.tag.join(', ')", slug: "tag" },
                { text: __('seals', 'event-table'), value: "seals.map((seal) => seal.name).join(', ')", slug: "seals"},
            ];

            return this.additionalHeaders || itens;
        },

        owner() {
            return new Entity('agent', $MAPAS.user.id);
        }

    },

    methods: {
        filterByLinguagem(entities) {
            if (this.selectedLinguagem.length > 0) {
                this.query['term:linguagem'] = `IIN(${this.selectedLinguagem.toString()})`;
            } else {
                delete this.query['term:linguagem'];
            }
            entities.refresh();
        },

        filterByAgeRating(entities) {
            if (this.selectedAgeRating.length > 0) {
                this.query['classificacaoEtaria'] = `IIN(${this.selectedAgeRating.toString()})`;
            } else {
                delete this.query['classificacaoEtaria'];
            }
            entities.refresh();
        },

        filterBySeals(entities) {
            if (this.selectedSeals.length > 0) {
                this.query['@seals'] = `${this.selectedSeals}`;
            } else {
                delete this.query['@seals'];
            }
            entities.refresh();
        },

        clearFilters(entities) {
            this.selectedLinguagem = [];
            this.selectedAgeRating = [];
            this.selectedSeals = [];
            delete this.query['term:linguagem'];
            delete this.query['classificacaoEtaria'];
            delete this.query['@seals'];

            entities.refresh();
        },

        removeFilter(filter) {
            switch (filter.prop) {
                case 'term:linguagem':
                    this.selectedLinguagem = this.selectedLinguagem.filter(term => term !== filter.value);
                    break;
                case 'classificacaoEtaria':
                    this.selectedAgeRating = this.selectedAgeRating.filter(rating => rating !== filter.value);
                    break;
                case '@seals':
                    delete this.query['@seals'];
                    this.selectedSeals = [];
                    break;
            }
        },
    },

});
