app.component('opportunity-table', {
    template: $TEMPLATES['opportunity-table'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('opportunity-table')
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
            '@select': 'name,type,shortDescription,files.avatar,seals,terms,registrationFrom,registrationTo',
            '@order': 'createTimestamp DESC',
            '@limit': 20,
            '@page': 1,
        }

        let visible = this.visibleColumns.join(',');

        let getSeals = $MAPAS.config.opportunityTable.seals;
        let seals = {}
        for (const seal of getSeals) {
            seals[seal.id] = seal.name;
        }

        return {
           selectedType: [],
           selectedArea: [],
           selectedSeals: [],
           visible,
           query,
           types: $DESCRIPTIONS.opportunity.type.options,
           terms: $TAXONOMIES.area.terms,
           seals,
           verified: undefined
        }
    },

    computed: {
        headers () {
            let itens = [
                { text: __('id', 'opportunity-table'), value: "id", sticky: true, width: '80px'},
                { text: __('name', 'opportunity-table'), value: "name", width: '160px' },
                { text: __('type', 'opportunity-table'), value: "type.name", slug: "type"},
                { text: __('area', 'opportunity-table'), value: "terms.area.join(', ')", slug: "area" },
                { text: __('tag', 'opportunity-table'), value: "terms.tag.join(', ')", slug: "tag" },
                { text: __('seals', 'opportunity-table'), value: "seals.map((seal) => seal.name).join(', ')", slug: "seals"},

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

        clearFilters(entities) {
            this.selectedType = [];
            this.selectedArea = [];
            this.selectedSeals = [];
            delete this.query['type'];
            delete this.query['term:area'];
            delete this.query['registrationFrom'];
            delete this.query['registrationTo'];
            delete this.query['@seals'];
            this.$refs.open.checked = false;
            this.$refs.closed.checked = false;
            this.$refs.future.checked = false;

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
                case 'registrationFrom':
                    delete this.query['registrationFrom'];
                    break;
                case 'registrationTo':
                    delete this.query['registrationTo'];
                    break;
            }
        },

        actualDate() {
            const data = new Date();
            const dia = String(data.getDate()).padStart(2, '0');
            const mes = String(data.getMonth() + 1).padStart(2, '0');
            const ano = data.getFullYear();
            return (ano + '-' + mes + '-' + dia);
        },

        openForRegistrations() {
            const currentDate = this.actualDate();
            this.query['registrationTo'] = `GTE(${currentDate})`;
            this.query['registrationFrom'] = `LTE(${currentDate})`;
        },

        closedForRegistrations() {
            const currentDate = this.actualDate();
            this.query['registrationTo'] = `LT(${currentDate})`;
            delete this.query['registrationFrom'];
        },

        futureRegistrations() {
            const currentDate = this.actualDate();
            this.query['registrationFrom'] = `GT(${currentDate})`;
            delete this.query['registrationTo'];
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
