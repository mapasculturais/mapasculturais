app.component('registration-field-address', {
    template: $TEMPLATES['registration-field-address'],

    emits: ['update:registration', 'change', 'save'],

    props: {
        registration: {
            type: Entity,
            required: true,
        },

        prop: {
            type: String,
            required: true,
        },

        autosave: {
            type: Number,
        },
        
        debounce: {
            type: Number,
            default: 0
        },

        disabled: {
            type: Boolean,
            default: false,
        },
    },    

    data() {
        const fieldConfiguration = this.registration.$PROPERTIES[this.prop].registrationFieldConfiguration;

        let rules = fieldConfiguration.config || {};
        let required = $DESCRIPTIONS.registration[this.prop].required;
        let title = fieldConfiguration.title;
        let description = fieldConfiguration.description;
        const statesAndCities = $MAPAS.config.statesAndCities;

        return {
            rules,
            required,
            title,
            description,
            statesAndCities,
        };
    },

    watch: {
        change(event, now) {
            clearTimeout(this.__timeout);
            let oldValue = this.entity[this.prop] ? JSON.parse(JSON.stringify(this.entity[this.prop])) : null;
            
            this.__timeout = setTimeout(() => {
                if (this.autosave && (now || JSON.stringify(this.entity[this.prop]) != JSON.stringify(oldValue))) {
                    this.entity.save(now ? 0 : this.autosave).then(() => {
                        this.$emit('save', this.entity);
                    });
                }

            }, now ? 0 : this.debounce);
        },
    },

    computed: {
        states() {
            const states = Object.keys(this.statesAndCities);
            return states.map(estado => this.statesAndCities[estado].label);
        },
    },
    
    methods: {
        async buscarEnderecoPorCep(address) {
            if (address.cep.length == 9) {
                const url = `https://viacep.com.br/ws/${address.cep}/json/`;

                try {
                    const response = await fetch(url);
                    const data = await response.json();

                    if (data.erro) {
                        console.error('CEP não encontrado');
                    } else {
                        address.logradouro = data.logradouro;
                        address.bairro = data.bairro;
                        address.complemento = data.complemento;
                        address.estado = data.estado;
                        address.cidade = data.localidade;
                    }
                } catch (error) {
                    console.error('Erro ao buscar endereço:', error);
                }
            }
        },

        cities(state) {
            for (const _state in this.statesAndCities) {
                if (this.statesAndCities[_state].label == state) {
                    return this.statesAndCities[_state].cities;
                }
            }

            return false;
        },

        stateError(address) {
            const errors = this.registration.__validationErrors[this.prop];
            return (errors?.some(str => str.toLowerCase().includes('estado')) && address.estado == '') ?? false;
        },

        cityError(address) {
            const errors = this.registration.__validationErrors[this.prop];
            return (errors?.some(str => str.toLowerCase().includes('cidade')) && address.cidade == '') ?? false;
        },

        addNewAddress() {
            if (!this.registration[this.prop]) {
                this.registration[this.prop] = [];
            }
 
            this.registration[this.prop].push({
                nome: '',
                cep: '',
                logradouro: '',
                numero: '',
                bairro: '',
                complemento: '',
                estado: '',
                cidade: '',
            }) 
        },

        removeAddress(address) {
            const addresses = this.registration[this.prop];
            this.registration[this.prop] = addresses.filter((_address) => {
                return !(_address == address);
            });
            this.save();
        },

        save() {
            this.registration.save();
            this.$emit('update:registration', this.registration);
        }
    },
});
