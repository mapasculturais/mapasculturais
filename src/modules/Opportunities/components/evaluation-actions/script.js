app.component('evaluation-actions', {
    template: $TEMPLATES['evaluation-actions'],
    emits: ['previousEvaluation', 'nextEvaluation'],

    props: {
        entity: {
            type: Entity,
            required: true
        },

        formData: {
            type: Object,
            required: true
        },
        
        validateErrors: {
            type: Function,
            required: true
        }
    },

    setup() {
        const text = Utils.getTexts('evaluation-actions')
        return { text }
    },

    mounted() {
        window.addEventListener('evaluationRegistrationList', this.getEvaluationList);
    },

    data() {
        return {
            evaluationRegistrationList: null,
            currentEvaluation: $MAPAS.config.evaluationActions?.currentEvaluation || null,
            oldEvaluation: null
        }
    },

    computed: {
        firstRegistration() {
            const globalState = useGlobalState();
            return globalState.firstRegistration;
        },

        lastRegistration() {
            const globalState = useGlobalState();
            return globalState.lastRegistration;
        },
    },

    methods: {
        buttonActionsActive(action){
            let reg = eval(`this.${action}?.registrationId`);
            return reg != this.entity.id;
        },
        getEvaluationList(data){
            if (data.detail.evaluationRegistrationList){
                this.evaluationRegistrationList = data.detail.evaluationRegistrationList;
            }
        },
        
        requestEvaluation(action, data = {}, args = {}, controller = 'registration') {
            return new Promise((resolve, reject) => {
                if (action == 'reopenEvaluation' || !this.validateErrors()) {
                    const api = new API(controller);
                    let url = api.createUrl(action, args);
                    let result = api.POST(url, data);
                    resolve(result);
                } 
            });
        },

        dispatchResponse(type, response) {
            this.oldEvaluation = this.currentEvaluation;
            this.currentEvaluation = response;
            window.dispatchEvent(new CustomEvent('responseEvaluation', {detail:{response: response, type: type}}));
        },

        saveEvaluation(finish = false) {
            const messages = useMessages();
            let args = {id: this.entity.id};

            if (finish) {
                args['status'] = 'evaluated';
            }

            this.requestEvaluation('saveEvaluation', this.formData, args).then(res => res.json()).then(response => {
                if (response.error) {
                    messages.error(response.data);
                } else {
                    this.dispatchResponse('saveEvaluation', response);

                    if (finish) {
                        messages.success(this.text('finish'));
                        this.updateSummaryEvaluations('completed');
                    } else {
                        messages.success(this.text('success'));
                        this.updateSummaryEvaluations('started');
                    }
                }
            });
        },

        sendEvaluation(){
            this.saveEvaluation(true);
            const messages = useMessages();
            let args = {id: this.entity.id};

            this.requestEvaluation('sendEvaluation', {data: this.formData}, args).then(res => res.json()).then(response => {
                if (response.error) {
                    messages.error(response.data);
                } else {
                    this.dispatchResponse('sendEvaluation', response);
                    this.updateSummaryEvaluations('sent');
                    messages.success(this.text('send'));
                }
            });
        },

        finishEvaluation() {
            this.saveEvaluation(true);
            this.updateSummaryEvaluations('completed');
        },

        finishEvaluationSend() {
            this.sendEvaluation();
            if (this.lastRegistration?.registrationid != this.entity.id){
                this.next();
            } 
        },

        finishEvaluationSendLater(){
            this.saveEvaluation(true);
            if (this.lastRegistration?.registrationid != this.entity.id){
                this.next();
            } 
        },

        reopen(){
            const messages = useMessages();
            let args = {id: this.entity.id};

            this.requestEvaluation('reopenEvaluation', {data: this.formData}, args).then(res => res.json()).then(response => {
                if (response.error) {
                    messages.error(response.data);
                } else {
                    this.dispatchResponse('reopenEvaluation', response);
                    messages.success(this.text('reopen'));
                    this.updateSummaryEvaluations('started');
                }
            });
        },

        previous() {
            window.dispatchEvent(new CustomEvent('previousEvaluation', {detail:{registrationId:this.entity.id}}));
        },

        next() {
            window.dispatchEvent(new CustomEvent('nextEvaluation', {detail:{registrationId:this.entity.id}}));
        },

        showActions(action) {
            let result = false;
            switch (action) {
                case 'finishEvaluation':
                case 'save':
                    if (!this.currentEvaluation) {
                        result = true;
                    }

                    if (this.currentEvaluation?.status < 1) {
                        result = true;
                    }

                    if (this.currentEvaluation?.status === undefined) {
                        result = true;
                    }

                    if (this.currentEvaluation?.status == "") {
                        result = true;
                    }
                    break;
                case 'send':
                case 'reopen':
                    result = this.currentEvaluation?.status == 1;
                    break;
                default:
                    result = false;
                    break;
            }
            return result;
        },

        updateSummaryEvaluations(newStatus) {     
            // remove status anterior
            if(!this.oldEvaluation) {
                this.oldEvaluation = {status: null}
            }
            
            switch(this.oldEvaluation.status) {
                case 0:
                    this.global.summaryEvaluations.started -= 1;
                    break;
                case 1:
                    this.global.summaryEvaluations.completed -= 1;
                    break;
                case 2:
                    this.global.summaryEvaluations.sent -= 1;
                    break;
                default:
                    this.global.summaryEvaluations.pending -= 1;
                    break;
            }
            
            // adiciona novo status
            switch(newStatus) {
                case 'pending':
                    this.global.summaryEvaluations.pending += 1;
                    break;
                case 'started':
                    this.global.summaryEvaluations.started += 1;
                    break;
                case 'completed':
                    this.global.summaryEvaluations.completed += 1;
                    break;
                case 'sent':
                    this.global.summaryEvaluations.sent += 1;
                    break;    
            }
        }
    }
});
