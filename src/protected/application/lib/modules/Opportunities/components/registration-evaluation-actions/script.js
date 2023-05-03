app.component('registration-evaluation-actions', {
    template: $TEMPLATES['registration-evaluation-actions'],
    emits: ['previousEvaluation', 'nextEvaluation'],
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

    computed: {
        firstRegistration(){
            const globalState = useGlobalState();
            return globalState.firstRegistration;
        },
        lastRegistration(){
            const globalState = useGlobalState();
            return globalState.lastRegistration;
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
            const iframe = document.getElementById('evaluation-form');
            iframe.contentWindow.postMessage({type: "evaluationForm.send", status: 'evaluated'});
        },
        saveAndContinue() {
            console.log(this.registration);
        },
        send() {
            console.log(this.registration);
        },
        previous() {
            window.dispatchEvent(new CustomEvent('previousEvaluation', {detail:{registrationId:this.registration.id}}));
        },
        next() {
            window.dispatchEvent(new CustomEvent('nextEvaluation', {detail:{registrationId:this.registration.id}}));
        },
        save() {
            const iframe = document.getElementById('evaluation-form');
            iframe.contentWindow.postMessage({type: "evaluationForm.save"});
        },
        exit() {
            console.log(this.registration);
        },
    },
});
