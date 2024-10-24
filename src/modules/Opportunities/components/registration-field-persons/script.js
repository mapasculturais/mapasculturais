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
            this.registration[this.prop].push({
                name: '',
                cpf: '',
                function: '',
                relationship: '',
            }) 
        },

        save() {
            this.registration.save();
            this.$emit('update:registration', this.registration);
        }
    },
});
