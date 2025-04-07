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
    },

    setup() {
        const globalState = useGlobalState();
        const messages = useMessages();
        const text = Utils.getTexts('evaluation-actions');
        return { globalState, messages, text };
    },

    mounted() {
        window.addEventListener('evaluationRegistrationList', this.getEvaluationList);
    },

    data() {
        return {
            evaluationRegistrationList: null,
            currentEvaluation: $MAPAS.config.evaluationActions?.currentEvaluation || null,
            oldEvaluation: null,
        }
    },

    computed: {
        firstRegistration() {
            return this.globalState.firstRegistration;
        },

        lastRegistration() {
            return this.globalState.lastRegistration;
        },
    },

    methods: {
        buttonActionsActive(action){
            const reg = this[action]?.registrationId;
            return reg != this.entity.id;
        },

        getEvaluationList(data){
            if (data.detail.evaluationRegistrationList){
                this.evaluationRegistrationList = data.detail.evaluationRegistrationList;
            }
        },

        async requestEvaluation(action, data = {}, args = {}, controller = 'registration') {
            return new Promise(async (resolve) => {
                if (action == 'reopenEvaluation' || !this.globalState.validateEvaluationErrors) {
                    const api = new API(controller);
                    const url = api.createUrl(action, args);
                    const res = await api.POST(url, data);
                    resolve(await res.json());
                }
            });
        },

        dispatchResponse(type, response) {
            this.oldEvaluation = this.currentEvaluation;
            this.currentEvaluation = response;
            window.dispatchEvent(new CustomEvent('responseEvaluation', { detail: { response, type } }));
        },

        dispatchErrors() {
            window.dispatchEvent(new CustomEvent('processErrors', { detail: {} }));
        },

        sendMessages (type, message) {
            this.messages[type](message);
        },

        async saveEvaluation({ disableMessages = false, finish = false } = {}) {
            const args = { id: this.entity.id };

            if (finish) {
                args.status = 'evaluated';
            }

            return this.requestEvaluation('saveEvaluation', this.formData, args).then(response => {
                if (response.error) {
                    this.sendMessage('error', response.data);
                } else {
                    this.dispatchResponse('saveEvaluation', response);
                    if (finish) {
                        if (!disableMessages) {
                            this.sendMessage('success', this.text('finish'));
                        }
                        this.updateSummaryEvaluations('completed');
                    } else {
                        if (!disableMessages) {
                            this.sendMessage('success', this.text('success'));
                        }
                        this.updateSummaryEvaluations('started');
                    }
                }
            });
        },

        async sendEvaluation(){
            const args = { id: this.entity.id };

            await this.saveEvaluation({ finish: true, disableMessages: true });

            return this.requestEvaluation('sendEvaluation', { data: this.formData }, args).then(response => {
                if (response.error) {
                    this.sendMessage('error', response.data);
                } else {
                    this.dispatchResponse('sendEvaluation', response);
                    this.updateSummaryEvaluations('sent');
                    this.sendMessage('success', this.text('send'));
                }
            });
        },

        async finishEvaluation() {
            this.dispatchErrors();
            await this.saveEvaluation({ finish: true });
            this.updateSummaryEvaluations('completed');
        },

        async finishEvaluationSend() {
            this.dispatchErrors();
            await this.sendEvaluation();
            if (this.lastRegistration?.registrationid != this.entity.id && !this.globalState.validateEvaluationErrors){
                this.next();
            } 
        },

        async finishEvaluationSendLater(){
            this.dispatchErrors();
            await this.saveEvaluation({ finish: true });
            if (this.lastRegistration?.registrationid != this.entity.id && !this.globalState.validateEvaluationErrors){
                this.next();
            } 
        },

        async reopen(){
            const args = { id: this.entity.id };

            return this.requestEvaluation('reopenEvaluation', { data: this.formData }, args).then(response => {
                if (response.error) {
                    this.sendMessage('error', response.data);
                } else {
                    this.dispatchResponse('reopenEvaluation', response);
                    this.sendMessage('success', this.text('reopen'));
                    this.updateSummaryEvaluations('started');
                }
            });
        },

        previous() {
            window.dispatchEvent(new CustomEvent('previousEvaluation', { detail: { registrationId: this.entity.id } }));
        },

        next() {
            window.dispatchEvent(new CustomEvent('nextEvaluation', { detail: { registrationId: this.entity.id } }));
        },

        showActions(action) {
            let result = false;
            if (action === 'finishEvaluation' || action === 'save') {
                if (!this.currentEvaluation) {
                    return true;
                }

                const status = this.currentEvaluation.status;
                if (status < 1 || status === undefined || status == '') {
                    return true;
                }
            } else if (action === 'send' || action === 'reopen') {
                return this.currentEvaluation?.status == 1;
            }
            return false;
        },

        updateSummaryEvaluations(newStatus) {
            // remove status anterior
            if (!this.oldEvaluation) {
                this.oldEvaluation = { status: null };
            }

            switch (this.oldEvaluation.status) {
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
            switch (newStatus) {
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
