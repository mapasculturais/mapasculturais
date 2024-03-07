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

            if (field == 'projectName') {
                return this.text('Nome do projeto');
            }

            if (field == 'space') {
                return this.text('Espaço');
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

            if(!registration.currentUserPermissions.evaluate){
                return false;
            }

            var result = false;
            this.evaluationRegistrationList.forEach(function(item){
                if(item.registrationid == registration.id){
                    switch (action) {
                        case 'finishEvaluation':
                        case 'save':
                            if(item.status < 1){
                                result = true;
                            }

                            if(item.status === undefined){
                                result =  true;
                            }

                            if(item.status == ""){
                                result =  true;
                            }
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
            const promise = this.evaluate();
            const messages = useMessages();

            promise.then(() => {
                messages.success(this.text('Avaliação salva'));
                this.reloadPage();
            }).catch((res) => {
                for (let error of res){
                    console.log(error)
                    messages.error(error);
                }
            })
        },
        send(registration) {
           this.sendEvaluation(registration);
           this.reloadPage();
        },
        reopen(registration){
            api = new API('registration');
            let url = api.createUrl('reopenEvaluation', {id: registration.id});

            var args = {};
            api.POST(url, args).then(res => res.json()).then(data => {
                const messages = useMessages();
                messages.success(this.text('Avaliação reaberta'));
                this.reloadPage();
            });
        },
        saveReload() {
            const promise = this.save();
            const messages = useMessages();

            promise.then(() => {
                this.reloadPage();
            }).catch((res) => {
                if(typeof res == "array") {
                    for (let error of res){
                        messages.error(error);
                    }
                }else {
                    messages.error(res);
                }
            })
        },
        finishEvaluationNext(registration) {
            const promise = this.evaluate();
            const messages = useMessages();
            let reload = false;

            promise.then(() => {
                this.next();
                this.sendEvaluation(registration);
                messages.success(this.text('Avaliação enviada'));
                reload = true;
            }).catch((res) => {
                for (let error of res){
                    reload = false;
                    console.log(error)
                    messages.error(error);
                }
            })

            if(reload && this.lastRegistration?.registrationid == registration.id){
                this.reloadPage();
            }
        },
        saveNext(registration) {
            const promise = this.save();
            const messages = useMessages();
            let reload = false;

            promise.then(() => {
                reload = true;
                messages.success(this.text('Avaliação salva'));
                this.next();
            }).catch((res) => {
                for (let error of res){
                    reload = false;
                    console.log(error)
                    messages.error(error);
                }
            })

            if(reload && this.lastRegistration?.registrationid == registration.id){
                this.reloadPage();
            }
        },
        save() {
            const iframe = document.getElementById('evaluation-form');
            iframe.contentWindow.postMessage({type: "evaluationForm.save"});

            return new Promise((resolve, reject) => {
                window.addEventListener("message", function(event) { 
                    if (event.data?.type == "evaluation.save.success") {
                        resolve();
                    }

                    if (event.data?.type == "evaluation.save.error") {
                        reject(event.data.error);
                    }
                });
            });
        },
        sendEvaluation(registration){
            api = new API('registration');
            let url = api.createUrl('sendEvaluation', {id: registration.id});

            var args = {};
            api.POST(url, args).then(res => res.json()).then(data => {
                const messages = useMessages();
                messages.success(this.text('Avaliação enviada'));
            });
        },
        evaluate() {
            const iframe = document.getElementById('evaluation-form');
            iframe.contentWindow.postMessage({type: "evaluationForm.send", status: 'evaluated'});

            return new Promise((resolve, reject) => {
                window.addEventListener("message", function(event) { 
                    if (event.data?.type == "evaluation.send.success") {
                        resolve();
                    }

                    if (event.data?.type == "evaluation.send.error") {
                        reject(event.data.error);
                    }
                });
            });
        },
        previous() {
            window.dispatchEvent(new CustomEvent('previousEvaluation', {detail:{registrationId:this.registration.id}}));
        },
        next() {
            window.dispatchEvent(new CustomEvent('nextEvaluation', {detail:{registrationId:this.registration.id}}));
        },
        reloadPage(timeout = 1500){
            setTimeout(() => {
                document.location.reload(true)
            }, timeout);
        },
    },
});
