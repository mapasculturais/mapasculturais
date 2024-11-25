app.component('select-municipio', {
    template: $TEMPLATES['select-municipio'],
    emits: ['change'],

    props: {
        entity: {
            type: Entity,
            required: true
        },

        prop: {
            type: String,
            required: true
        },
    },

    data() {
        return {
            ibge: $MAPAS.ibge,
            states: [],
            cities: [],
            selectedState: null,
            selectedCity: null,
        }
    },

    methods: {
        loadStates() {
            this.states = Object.values(this.ibge);
        },

        loadCities(option) {
            this.selectedState = option.value;
            const state = this.ibge[this.selectedState];

            this.cities = state ? state.municipios : [];
            this.selectedCity = null;
        },

        selectCity(option) {
            let city = option.value;
            this.selectedCity = `${city}, ${this.selectedState}`;
            this.$emit('change', this.selectedCity);
        },

        loadData() {
            const existingValue = this.entity[this.prop] || '';

            if (existingValue) {
                const [city, state] = existingValue.split(',').map(s => s.trim());
                const stateData = Object.values(this.ibge).find(s => s.sigla === state);

                if (stateData) {
                    this.selectedState = state;
                    this.cities = stateData.municipios;

                    const cityExists = this.cities.some(c => c.nome === city);

                    if (cityExists) {
                        this.selectedCity = city;
                    }
                }
            }
        }
    },

    mounted() {
        this.loadStates();
        this.loadData();
    }
});
