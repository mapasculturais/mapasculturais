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
        removeAddress(address) {
            const addresses = this.registration[this.prop];
            this.registration[this.prop] = addresses.filter( (_address) => { 
                return !(_address == address);
            });
            this.save();
        },

        cities(state) {
            for (const _state in this.statesAndCities) {
                if (this.statesAndCities[_state].label == state) {
                    return this.statesAndCities[_state].cities;
                }
            }

            return false;
        },

        addNewAddress() {
            if (!this.registration[this.prop]) {
                this.registration[this.prop] = [];
            }
 
            this.registration[this.prop].push({
                cep: '',
                logradouro: '',
                numero: '',
                bairro: '',
                complemento: '',
                estado: '',
                cidade: '',
            }) 
        },

        save() {
            this.registration.save();
            this.$emit('update:registration', this.registration);
        }
    },
});
