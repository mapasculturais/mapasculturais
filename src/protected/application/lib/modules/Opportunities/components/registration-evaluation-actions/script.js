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
        window.addEventListener('evaluationRegistrationList', this.getEvaluationList);
    },

    data() {
        return {
            fields: $MAPAS.registrationFields,
            evaluationRegistrationList: null
            
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
        getEvaluationList(data){
            if(data.detail.evaluationRegistrationList){
                this.evaluationRegistrationList = data.detail.evaluationRegistrationList;
            }
        },
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
        showActions(registration, action){
            var result = false;
            this.evaluationRegistrationList.forEach(function(item){
                if(item.registrationid == registration.id){
                    switch (action) {
                        case 'finishEvaluation':
                        case 'save':
                            result = item.status < 1;
                            break;
                        case 'send':
                        case 'reopen':
                            result = item.status == 1;
                            break;
                        default:
                            result = false;
                            break;
                    }
                }
            });

            return result;
        },
        finishEvaluation() {
            const iframe = document.getElementById('evaluation-form');
            iframe.contentWindow.postMessage({type: "evaluationForm.send", status: 'evaluated'});
            this.reloadPage();
        },
        saveAndContinue() {
            console.log(this.registration);
        },
        reopen(registration){
            api = new API('registration');
            let url = api.createUrl('reopenEvaluation', {id: registration.id});

            var args = {};
            api.POST(url, args).then(res => res.json()).then(data => {
                messages.success(this.text('Avaliação reaberta'));
            });
            this.reloadPage();
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
