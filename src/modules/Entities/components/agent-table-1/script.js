app.component('agent-table-1', {
    template: $TEMPLATES['agent-table-1'],
    
    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('agent-table-1')
        return { text }
    },

    props: {
        visibleColumns: {
            type: Array,
            default: ["name", "id"],
        },

        hideFilters: {
            type: Boolean,
            default: false
        },
    },

    data() {
        
        return {
            terms: $TAXONOMIES.area.terms,
            selectedSexualOrientation: [],
            selectedGender: [],
            selectedRace: [],
            sexualOrientation: $DESCRIPTIONS.agent.orientacaoSexual.optionsOrder,
            gender: $DESCRIPTIONS.agent.genero.optionsOrder,
            race: $DESCRIPTIONS.agent.raca.optionsOrder,
            extraQuery: {},
        }
    },

    computed: {
        headers () {
            let itens = [
                { text: __('orientacaoSexual', 'agent-table-1'), value: "orientacaoSexual", slug: "orientacaoSexual" },
                { text: __('genero', 'agent-table-1'), value: "genero", slug: "genero" },
                { text: __('raca', 'agent-table-1'), value: "raca", slug: "raca" },
            ];

            return itens;  
        },
    },
    
    methods: {
        filterByOrientacaoSexual(entities) {
            if (this.selectedSexualOrientation.length > 0) {
                this.extraQuery['orientacaoSexual'] = `IIN(${this.selectedSexualOrientation.join(", ")})`;
            } else {
                delete this.extraQuery['orientacaoSexual'];
            }
            entities.refresh();
        },

        filterByGender(entities) {
            if (this.selectedGender.length > 0) {
                this.extraQuery['genero'] = `IIN(${this.selectedGender.join(", ")})`;
            } else {
                delete this.extraQuery['genero'];
            }
            entities.refresh();
        },

        filterByRace(entities) {
            if (this.selectedRace.length > 0) {
                this.extraQuery['raca'] = `IIN(${this.selectedRace.join(", ")})`;
            } else {
                delete this.extraQuery['raca'];
            }
            entities.refresh();
        },

        oldPeopleFilter(event, entities) {
            if (event.target.checked) {
                this.extraQuery['idoso'] = 'EQ(1)';
            } else {
                delete this.extraQuery['idoso'];
            }
            entities.refresh();
        },

        clearFilters(entities) {
            this.selectedSexualOrientation = [];
            this.selectedGender = [];
            this.selectedRace = [];
            delete this.extraQuery['orientacaoSexual'];
            delete this.extraQuery['genero'];
            delete this.extraQuery['raca'];
            delete this.extraQuery['idoso'];

            this.$refs.oldPeople.checked = false;

            entities.refresh();
        },

        removeFilter(filter) {
            switch (filter.prop) {
                case 'orientacaoSexual':
                    this.selectedSexualOrientation = this.selectedSexualOrientation.filter(orientacao => orientacao !== filter.value);
                    break;
                case 'genero':
                    this.selectedGender = this.selectedGender.filter(gen => gen !== filter.value);
                    break;
                case 'raca':
                    this.selectedRace = this.selectedRace.filter(raca => raca !== filter.value);
                    break;
                case 'idoso':
                    this.$refs.oldPeople.checked = false;
                    break;
            }
            delete this.extraQuery[filter.prop];
        },
    }
});
