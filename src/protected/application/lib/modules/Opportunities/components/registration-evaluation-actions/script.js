app.component('registration-evaluation-actions', {
    template: $TEMPLATES['registration-evaluation-actions'],

    props: {
        registration: {
            type: Entity,
            required: true
        },
    },

    setup() {
        const text = Utils.getTexts('registration-evaluation-actions')
        return { text }
    },

    mounted() {
    },

    data() {
        return {
            fields: $MAPAS.registrationFields,
        }
    },
    
    methods: {
        fieldName(field) {
            if (field == 'agent_instituicao') {
                return this.text('Instituição responsável');
            }

            if (field == 'agent_coletivo') {
                return this.text('Agente coletivo');
            }

            if (field.slice(0, 6) == 'field_') {
                for (let regField of this.fields) {
                    if (regField.fieldName == field) {
                        return regField.title;
                    }
                }
            }

            return this.text('Campo não identificado');

        },
        finishEvaluation() {
            console.log(this.registration);
        },
        saveAndContinue() {
            console.log(this.registration);
        },
        send() {
            console.log(this.registration);
        },
        previous() {
            console.log(this.registration);
        },
        next() {
            console.log(this.registration);
        },
        save() {
            console.log(this.registration);
        },
        exit() {
            console.log(this.registration);
        },
    },
});
