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

        // estados forçados pela instalação: alimentam a lista de cidades quando
        // não há seleção do usuário, sem serem emitidos no model nem renderizados
        // como seleção
        lockedStates: {
            type: Array,
            default: () => [],
        },

        hideStates: {
            type: Boolean,
            default: false,
        },

        hideCities: {
            type: Boolean,
            default: false,
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
            let estados = Object.fromEntries(
                Object.entries($MAPAS.config.statesAndCities).map(([UF, estado]) => [UF, estado.label])
            );

            // recorte da instalação: com locked-states, o seletor lista apenas as UFs travadas
            // (defesa contra o dataset completo publicado por outros componentes, ex.: agent-table)
            if (this.lockedStates.length) {
                const locked = new Set(this.lockedStates);
                estados = Object.fromEntries(Object.entries(estados).filter(([UF]) => locked.has(UF)));
            }

            return estados;
        },

        cities() {
            let cidades = {};

            // fonte da lista de cidades: seleção do usuário ou, na ausência desta,
            // os estados travados pela instalação (lockedStates)
            const sourceStates = this.selectedStates.length ? this.selectedStates : this.lockedStates;

            if (sourceStates.length == 1) {
                const state = sourceStates[0];
                if ($MAPAS.config.statesAndCities[state]) {
                    for (const city of $MAPAS.config.statesAndCities[state].cities) {
                        cidades[city] = city;
                    }
                }
            }

            if (sourceStates.length > 1) {
                for (const state of sourceStates) {
                    if (!$MAPAS.config.statesAndCities[state]) {
                        continue;
                    }
                    for (const city of $MAPAS.config.statesAndCities[state].cities) {
                        cidades[city] = city + ' - ' + state;
                    }
                }
            }

            return cidades;
        },

        citiesDisabled() {
            return this.selectedStates.length == 0 && this.lockedStates.length == 0;
        },
    },
});
