app.component('agent-table', {
    template: $TEMPLATES['agent-table'],
    emits: ['clear-filters', 'remove-filter'],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('__template__')
        return { text, hasSlot }
    },

    props: {
        visibleColumns: {
            type: Array,
            default: ["name", "type", "area", "id"],
        },

        hideFilters: {
            type: Boolean,
            default: false
        },

        agentType: {
            type: Number,
        },

        additionalHeaders: {
            type: Array,
            default: [],
        },

        extraQuery: {
            type: Object,
            default: () => ({}),
        }, 
    },

    data() {
        const defaultHeaders = $MAPAS.config.agentTable.defaultHeaders;
        const _additionalHeaders = (this.additionalHeaders.length > 0) ? this.additionalHeaders : $MAPAS.config.agentTable.additionalHeaders;

        const mergedHeaders = [...defaultHeaders, ..._additionalHeaders];
        
        let select = [];
        for(item of mergedHeaders) {
            if(item.slug) {
                select.push(item.slug)
            }
        }
        
        let query = {
            '@select': `name,type,shortDescription,files.avatar,seals,endereco,terms,orientacaoSexual,genero,raca,${select}`,
            '@order': 'createTimestamp DESC',
            '@limit': 20,
            '@page': 1,
        }

        if (this.agentType) {
            query['type'] = `EQ(${this.agentType})`;
        }

        let getSeals = $MAPAS.config.agentTable.seals;
        let seals = {}
        for (const seal of getSeals) {
            seals[seal.id] = seal.name;
        }

        const getCities = $MAPAS.config.statesAndCities;

        let municipio = [];

        return {
            mergedHeaders,
            terms: $TAXONOMIES.area.terms,
            types: $DESCRIPTIONS.agent.type.options,
            state: $DESCRIPTIONS.agent.En_Estado.optionsOrder,
            query,
            selectedArea: [],
            selectedSeals: [],
            selectedBairro: [],
            selectedCities: [],
            selectedState: [],
            seals,
            getCities,
            municipio,
            verified: undefined
        }
    },

    computed: {
        mergedQuery() {
            return {...this.extraQuery, ...this.query};
        },

        headers () {
            let itens = this.mergedHeaders;
            
            if (!this.agentType) {
                itens.push({ text: __('type', 'agent-table'), value: "type.name", slug: "type"});
            }

            return itens;
        },

        cities() {
            return this.municipio;
        },

        owner() {
            const global = useGlobalState();
            return new Entity('agent', global.auth.user?.profile.id);
        },

    },
    
    methods: {
        filterByArea(entities) {
            if (this.selectedArea.length > 0) {
                this.query['term:area'] = `IN(${this.selectedArea.toString()})`;
            } else {
                delete this.query['term:area'];
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

        filterByBairro(entities) {
            if (this.selectedBairro.length > 0) {
                this.query['En_Bairro'] = `IN(${this.selectedBairro.toString()})`;
            } else {
                delete this.query['En_Bairro'];
            }
            entities.refresh();
        },

        filterByState(entities) {
            if (this.selectedState.length > 0) {
                this.query['En_Estado'] = `IN(${this.selectedState.toString()})`;
            } else {
                delete this.query['En_Estado'];
            }

            const sigla = Object.keys(this.getCities);
            sigla.filter((item) => {
                if (item == this.selectedState[0]) {
                    this.municipio.push(...this.getCities[item].cities);
                }
            });

            entities.refresh();
        },

        filterByCities(entities) {
            query = {
                '@select': 'name,type,shortDescription,files.avatar,seals,endereco,terms',
                '@order': 'createTimestamp DESC',
                '@limit': 20,
                '@page': 1,
                'En_Estado': `IN(${this.selectedState.toString()})`,
            };

            if (this.selectedCities.length > 0) {
                this.query['En_Municipio'] = `IN(${this.selectedCities.toString()})`;
            } else {
                delete this.query['En_Municipio'];
            }
            entities.refresh();
        },

        clearFilters(entities) {
            this.selectedArea = [];
            this.selectedSeals = [];
            this.selectedBairro = [];
            this.selectedCities = [];
            this.selectedState = [];
            delete this.query['term:area'];
            delete this.query['@seals'];
            delete this.query['En_Bairro'];
            delete this.query['En_Estado'];
            delete this.query['En_Municipio'];

            entities.refresh();
            this.$emit('clear-filters', entities);
        },

        removeFilter(filter) {
            switch (filter.prop) {
                case 'term:area':
                    this.selectedArea = this.selectedArea.filter(area => area !== filter.value);
                    break;
                case '@seals':
                    delete this.query['@seals'];
                    this.selectedSeals = [];
                    break;
                case 'En_Bairro':
                    this.selectedBairro = this.selectedBairro.filter(bairro => bairro !== filter.value);
                    break;
                case 'En_Estado':
                    this.selectedState = this.selectedState.filter(state => state !== filter.value);
                    this.municipio = [];
                    break;
                case 'En_Municipio':
                    this.selectedCities = this.selectedCities.filter(city => city !== filter.value);
                    break;
            }
            delete this.query[filter.prop];
            this.$emit('remove-filter', filter);
        },

        getVerified() {
            if(this.verified === 1) {
                this.query['@verified'] = this.verified;
            } else {
                delete this.query['@verified'];
            }
        }
    }
    
});
