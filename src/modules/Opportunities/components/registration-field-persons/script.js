app.component('registration-field-persons', {
    template: $TEMPLATES['registration-field-persons'],
    
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
        let rules = this.registration.$PROPERTIES[this.prop].registrationFieldConfiguration.config || {};
        let isFieldRequired = rules.requiredFields || {};
        let required = $DESCRIPTIONS.registration[this.prop].required;
        let title = this.registration.$PROPERTIES[this.prop].registrationFieldConfiguration.title;
        let description = this.registration.$PROPERTIES[this.prop].registrationFieldConfiguration.description;

        return {
            rules,
            required,
            isFieldRequired,
            title,
            description,
            areas: $TAXONOMIES.area.terms,
            functions: $TAXONOMIES.funcao.terms,
            races: $DESCRIPTIONS.agent.raca.optionsOrder,
            genders: $DESCRIPTIONS.agent.genero.optionsOrder,
            sexualOrientations: $DESCRIPTIONS.agent.orientacaoSexual.optionsOrder,
            deficiencies: $DESCRIPTIONS.agent.pessoaDeficiente.optionsOrder,
            communities: $DESCRIPTIONS.agent.comunidadesTradicional.optionsOrder,
            education: $DESCRIPTIONS.agent.escolaridade.optionsOrder,
            income: $DESCRIPTIONS.agent.renda.optionsOrder,
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
    
    methods: {
        removePerson(person) {
            const persons = this.registration[this.prop];
            this.registration[this.prop] = persons.filter( (_person) => { 
                return !(_person == person);
            });
            this.save();
        },

        addNewPerson() {
            if (!this.registration[this.prop]) {
                this.registration[this.prop] = [];
            }
 
            this.registration[this.prop].push({
                name: '',
                fullName: '',
                socialName: '',
                cpf: '',
                income: '',
                education: '',
                telephone: '',
                email: '',
                race: '',
                gender: '',
                sexualOrientation: '',
                deficiencies: [],
                comunty: '',
                area: [],
                funcao: [],
            }) 
        },

        fieldError(person, field) {
            const fieldNames = {
                'name': 'Nome',
                'fullName': 'Nome Completo',
                'socialName': 'Nome Social',
                'cpf': 'CPF',
                'income': 'Renda',
                'education': 'Escolaridade',
                'telephone': 'Telefone do representante',
                'email': 'Email',
                'race': 'Raça / Cor',
                'gender': 'Genero',
                'sexualOrientation': 'Orientação sexaul',
                'deficiencies': 'deficiências',
                'comunty': 'povos ou comunidades',
                'area': 'Áreas de atuação',
                'funcao': 'funções/profissões',
            };

            const errors = this.registration.__validationErrors[this.prop];
            return (errors?.some(str => str.toLowerCase().includes(fieldNames[field])) && person[field] == '') ?? false;
        },

        save() {
            this.registration.save();
            this.$emit('update:registration', this.registration);
        }
    },
});
