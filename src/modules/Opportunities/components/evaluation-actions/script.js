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
            currentEvaluation: $MAPAS.config.simpleEvaluationForm?.currentEvaluation || null
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
        getEvaluationList(data){
            if (data.detail.evaluationRegistrationList){
                this.evaluationRegistrationList = data.detail.evaluationRegistrationList;
            }
        },
        
        requestEvaluation(action, data = {}, args = {}, controller = 'registration') {
            return new Promise((resolve, reject) => {
                if (!this.validateErrors()) {
                    const api = new API(controller);
                    let url = api.createUrl(action, args);
                    let result = api.POST(url, data);
                    resolve(result);
                } 
            });
        },

        dispatchResponse(type, response) {
            this.currentEvaluation = response;
            window.dispatchEvent(new CustomEvent('responseEvaluation', {detail:{response: response, type: type}}));
        },

        saveEvaluation(finish = false) {
            const messages = useMessages();
            let args = {id: this.entity.id};

            if (finish) {
                args['status'] = 'evaluated';
            }

            this.requestEvaluation('saveEvaluation', {data: this.formData}, args).then(res => res.json()).then(response => {
                this.dispatchResponse('saveEvaluation', response);
                finish ? messages.success(this.text('finish')) : messages.success(this.text('success'));
            });
        },

        sendEvaluation(registration){
            const messages = useMessages();
            api = new API('registration');
            let url = api.createUrl('sendEvaluation', {id: registration.id});
            if (!this.validateErrors(this.formData)) {
                api.POST(url, {data: this.formData}).then(res => res.json()).then(response => {
                    this.dispatchResponse('sendEvaluation', response);
                    messages.success(this.text('send'));
                });
            }
        },

        finishEvaluation(registration) {
            this.saveEvaluation(true);
            this.reloadPage();
        },

        finishEvaluationSend(registration) {
            this.sendEvaluation(registration);
            if (this.lastRegistration?.registrationid != registration.id){
                this.next();
            } else {
                this.reloadPage();
            }
        },

        finishEvaluationSendLater(registration){
            this.saveEvaluation(true);
            if (this.lastRegistration?.registrationid != registration.id){
                this.next();
            } else if (this.lastRegistration?.registrationid == registration.id){
                this.reloadPage();
            }
        },
        
        reloadPage() {
            window.location.reload();
        },

        reopen(registration){
            const messages = useMessages();
            api = new API('registration');
            let url = api.createUrl('reopenEvaluation', {id: registration.id});
            const args = {};

            api.POST(url, args).then(res => res.json()).then(response => {
                this.dispatchResponse('reopenEvaluation', response);
                messages.success(this.text('reopen'));
                this.reloadPage();
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
    }
});
