app.component('mc-states-and-cities', {
    template: $TEMPLATES['mc-states-and-cities'],

    // define os eventos que este componente emite
    emits: ['update:modelStates', 'update:modelCities'],

    props: {
        modelStates: {
            type: Array,
            default: [],
        },
        
        modelCities: {
            type: Array,
            default: [],
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
            },
            deep: true,
        },

        selectedCities: {
            handler(value) {
                this.$emit('update:modelCities', value);
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
