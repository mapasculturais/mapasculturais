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

        extraQuery: {
            type: Object,
            default: () => ({}),
        }, 
    },

    data() {
        return {
            terms: $TAXONOMIES.area.terms,
            selectedSexualOrientation: [],
            selectedGender: [],
            selectedRace: [],
            sexualOrientation: $DESCRIPTIONS.agent.orientacaoSexual.optionsOrder.filter((value) => value != ''),
            gender: $DESCRIPTIONS.agent.genero.optionsOrder.filter((value) => value != ''),
            race: $DESCRIPTIONS.agent.raca.optionsOrder.filter((value) => value != ''),
            localExtraQuery: { ...this.extraQuery }
        }
    },

    computed: {
        additionalHeaders () {
            return $MAPAS.config.agentTable1.additionalHeaders;  
        },
    },
    
    methods: {
        filterByOrientacaoSexual(entities) {
            if (this.selectedSexualOrientation.length > 0) {
                this.localExtraQuery = {...this.localExtraQuery, orientacaoSexual: `IIN(${this.selectedSexualOrientation.join(", ")})`};
            } else {
                this.localExtraQuery = {...this.localExtraQuery, orientacaoSexual: undefined};
            }
            entities.refresh();
        },

        filterByGender(entities) {
            if (this.selectedGender.length > 0) {
                this.localExtraQuery = {...this.localExtraQuery, genero: `IIN(${this.selectedGender.join(", ")})`};
            } else {
                this.localExtraQuery = {...this.localExtraQuery, genero: undefined};
            }
            entities.refresh();
        },

        filterByRace(entities) {
            if (this.selectedRace.length > 0) {
                this.localExtraQuery = {...this.localExtraQuery, raca: `IN(${this.selectedRace.join(", ")})`};
            } else {
                this.localExtraQuery = {...this.localExtraQuery, raca: undefined};
            }
            entities.refresh();
        },

        oldPeopleFilter(event, entities) {
            if (event.target.checked) {
                this.localExtraQuery = {...this.localExtraQuery, idoso: `EQ(1)`};
            } else {
                this.localExtraQuery = {...this.localExtraQuery, idoso: undefined};
            }
            entities.refresh();
        },

        clearFilters(entities) {
            this.selectedSexualOrientation = [];
            this.selectedGender = [];
            this.selectedRace = [];
            delete this.localExtraQuery['orientacaoSexual'];
            delete this.localExtraQuery['genero'];
            delete this.localExtraQuery['raca'];
            delete this.localExtraQuery['idoso'];

            this.$refs.oldPeople.checked = false;

            entities.refresh();
        },

        removeFilter(filter) {
            switch (filter.prop) {
                case 'orientacaoSexual':
                    this.selectedSexualOrientation = this.selectedSexualOrientation.filter((orientacao) => orientacao.toString().trim() != filter.value.toString().trim());
                    if (this.selectedSexualOrientation.length > 0) {
                        this.localExtraQuery = {...this.localExtraQuery, orientacaoSexual: `IIN(${this.selectedSexualOrientation.join(", ")})`};
                    } else {
                        delete this.localExtraQuery['orientacaoSexual'];
                    }
                    break;
                case 'genero':
                    this.selectedGender = this.selectedGender.filter((gen) => gen.toString().trim() != filter.value.toString().trim());
                    if (this.selectedGender.length > 0) {
                        this.localExtraQuery = {...this.localExtraQuery, genero: `IIN(${this.selectedGender.join(", ")})`};
                    } else {
                        delete this.localExtraQuery['genero'];
                    }
                    break;
                case 'raca':
                    this.selectedRace = this.selectedRace.filter((raca) => raca.toString().trim() != filter.value.toString().trim());
                    if (this.selectedRace.length > 0) {
                        this.localExtraQuery = {...this.localExtraQuery, raca: `IN(${this.selectedRace.join(", ")})`};
                    } else {
                        delete this.localExtraQuery['raca'];
                    }
                    break;
                case 'idoso':
                    this.$refs.oldPeople.checked = false;
                    delete this.localExtraQuery['idoso'];
                    break;
            }
        },
    }
});
