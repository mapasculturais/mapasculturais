app.component('project-table', {
    template: $TEMPLATES['project-table'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('project-table')
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
            '@select': 'name,type,shortDescription,files.avatar,seals,terms',
            '@order': 'createTimestamp DESC',
            '@limit': 20,
            '@page': 1,
        }

        let visible = this.visibleColumns.join(',');

        let getSeals = $MAPAS.config.projectTable.seals;
        let seals = {}
        for (const seal of getSeals) {
            seals[seal.id] = seal.name;
        }

        return {
           selectedType: [],
           selectedSeals: [],
           visible,
           query,
           types: $DESCRIPTIONS.project.type.options,
           seals,
           verified: undefined
        }
    },

    computed: {
        headers () {
            let itens = [
                { text: __('id', 'project-table'), value: "id", sticky: true, width: '80px'},
                { text: __('name', 'project-table'), value: "name", width: '160px' },
                { text: __('type', 'project-table'), value: "type.name", slug: "type"},
                { text: __('tag', 'project-table'), value: "terms.tag.join(', ')", slug: "tag" },
                { text: __('seals', 'project-table'), value: "seals.map((seal) => seal.name).join(', ')", slug: "seals"},

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
            this.selectedSeals = [];
            delete this.query['type'];
            delete this.query['@seals'];

            entities.refresh();
        },

        removeFilter(filter) {
            switch (filter.prop) {
                case 'type':
                    this.selectedType = this.selectedType.filter(type => type !== filter.value);
                    break;
                case '@seals':
                    delete this.query['@seals'];
                    this.selectedSeals = [];
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