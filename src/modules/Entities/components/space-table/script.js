app.component('space-table', {
    template: $TEMPLATES['space-table'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('space-table')
        return { text }
    },

    props: {
        visibleColumns: {
            type: Array,
            default: ["id", "name", "type", "area", "tag", "seals"],
        },

        hideFilters: {
            type: Boolean,
            default: false
        },
    },

    data() {
        let query = {
            '@select': 'name,type,shortDescription,files.avatar,seals,endereco,terms,acessibilidade',
            '@order': 'createTimestamp DESC',
            '@limit': 20,
            '@page': 1,
        }

        let visible = this.visibleColumns.join(',');

        let getSeals = $MAPAS.config.spaceTable.seals;
        let seals = {}
        for (const seal of getSeals) {
            seals[seal.id] = seal.name;
        }

        return {
           selectedType: [],
           selectedArea: [],
           selectedSeals: [],
           selectedAccessibility: false,
           visible,
           query,
           types: $DESCRIPTIONS.space.type.options,
           terms: $TAXONOMIES.area.terms,
           seals,
           verified: undefined
        }
    },

    computed: {
        headers () {
            let itens = [
                { text: __('id', 'space-table'), value: "id", sticky: true, width: '80px'},
                { text: __('name', 'space-table'), value: "name", width: '160px' },
                { text: __('type', 'space-table'), value: "type.name", slug: "type"},
                { text: __('area', 'space-table'), value: "terms.area.join(', ')", slug: "area" },
                { text: __('tag', 'space-table'), value: "terms.tag.join(', ')", slug: "tag" },
                { text: __('seals', 'space-table'), value: "seals.map((seal) => seal.name).join(', ')", slug: "seals"},

            ];

            return itens;
        },

        owner() {
            return new Entity('agent', $MAPAS.user.id);
        }

    },
    
    methods: {
        filterByType(entities) {
            if (this.selectedType.length > 0) {
                this.query['type'] = `IN(${this.selectedType.toString()})`;
            } else {
                delete this.query['type'];
            }

            entities.refresh();
        },

        filterByArea(entities) {
            if (this.selectedArea.length > 0) {
                this.query['term:area'] = `IIN(${this.selectedArea.toString()})`;
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

        filterByAccessibility(event, entities) {
            if (event.target.checked) {
                this.query['acessibilidade'] = `EQ(Sim)`;
                this.$refs.acessibility.checked = true;
            } else {
                delete this.query['acessibilidade'];
            }
            entities.refresh();
        },

        clearFilters(entities) {
            this.selectedType = [];
            this.selectedSeals = [];
            this.selectedArea = [];
            delete this.query['type'];
            delete this.query['@seals'];
            delete this.query['term:area'];
            delete this.query['acessibilidade'];

            this.$refs.acessibility.checked = false;

            entities.refresh();
        },

        removeFilter(filter) {
            switch (filter.prop) {
                case 'type':
                    this.selectedType = this.selectedType.filter(type => type !== filter.value);
                    break;
                case 'term:area':
                    this.selectedArea = this.selectedArea.filter(area => area !== filter.value);
                    break;
                case '@seals':
                    delete this.query['@seals'];
                    this.selectedSeals = [];
                    break;
                case 'acessibilidade':
                    this.$refs.acessibility.checked = false;
                    break;
            }
        },

        getVerified() {
            if(this.verified === 1) {
                this.query['@verified'] = this.verified;
            } else {
                delete this.query['@verified'];
            }
        }
    },
    
});