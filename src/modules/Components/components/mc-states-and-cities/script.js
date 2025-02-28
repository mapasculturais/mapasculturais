app.component('mc-states-and-cities', {
    template: $TEMPLATES['mc-states-and-cities'],

    // define os eventos que este componente emite
    emits: ['update:modelStates', 'update:modelCities', 'changeStates', 'changeCities'],

    props: {
        modelStates: {
            type: Array,
            default: [],
        },
        
        modelCities: {
            type: Array,
            default: [],
        },

        fieldClass: {
            type: String || Array,
            default: '',
        },

        hideLabels: {
            type: Boolean,
            default: false,
        },

        hideTags: {
            type: Boolean,
            default: false,
        },

        statePlaceholder: {
            type: String,
            default: 'Busque ou selecione os estados',
        },

        cityPlaceholder: {
            type: String,
            default: 'Busque ou selecione as cidades',
        },
    },

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        return { hasSlot }
    },

    data() {
        return {
            selectedStates: [],
            selectedCities: [],
        };
    },

    watch: {
        selectedStates: {
            handler(value) {
                // limpar cidades selecionadas de um estado específico caso o estado seja removido
                this.selectedCities = this.selectedCities.filter(city => city in this.cities);
                this.$emit('update:modelStates', value);
                this.$emit('changeStates', value);
            },
            deep: true,
        },

        modelStates: {
            handler(value) {
                this.selectedStates = this.modelStates;
                this.$emit('update:modelStates', value);
                this.$emit('changeCities', value);
            },
            deep: true,
        },

        selectedCities: {
            handler(value) {
                this.$emit('update:modelCities', value);
                this.$emit('changeCities', value);
            },
            deep: true,
        },

        modelCities: {
            handler(value) {
                this.selectedCities = this.modelCities;
                this.$emit('update:modelCities', value);
                this.$emit('changeCities', value);
            },
            deep: true,
        },
    },

    computed: {
        states() {
            const estados = Object.fromEntries(
                Object.entries($MAPAS.config.statesAndCities).map(([UF, estado]) => [UF, estado.label])
            );

            return estados;
        },

        cities() {
            let cidades = {};

            if (this.selectedStates.length == 1) {
                const state = this.selectedStates[0];
                for (let i = 0; i < $MAPAS.config.statesAndCities[state].cities.length; i++) {
                    cidades[$MAPAS.config.statesAndCities[state].cities[i]] = $MAPAS.config.statesAndCities[state].cities[i];
                }
            }

            if (this.selectedStates.length > 1) {
                for (state of this.selectedStates) {
                    for (let i = 0; i < $MAPAS.config.statesAndCities[state].cities.length; i++) {
                        cidades[$MAPAS.config.statesAndCities[state].cities[i]] = $MAPAS.config.statesAndCities[state].cities[i] + ' - ' + state;
                    }
                }
            }

            return cidades;
        },
    },
});
