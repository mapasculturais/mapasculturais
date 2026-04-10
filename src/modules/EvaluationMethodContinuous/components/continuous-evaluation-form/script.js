app.component('continuous-evaluation-form', {
    template: $TEMPLATES['continuous-evaluation-form'],

    setup() {
        const text = Utils.getTexts('continuous-evaluation-form')
        const global = useGlobalState();
        return { text, global }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },

        editable: {
            type: Boolean,
            default: true
        },

        formData: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            isEditable: true,
            threadId: $MAPAS.config.continuousEvaluationForm?.threadId,
        };
    },

    created() {
        this.formData['data'] = {};
        const formData = this.evaluationData || this.skeleton();

        for (let key in formData.data) {
            this.formData.data[key] = formData.data[key];
        }

        if (!this.global.threads) {
            this.global.threads = {};
            this.global.threads[this.threadId] = $MAPAS.config.continuousEvaluationForm.lastChatMessage;
        }

        this.handleCurrentEvaluationForm();
    },

    mounted() {        
        window.addEventListener('responseEvaluation', this.processResponse);
        window.addEventListener('processErrors', this.validateErrors);
    },

    computed: {
        statusList() {
            return $MAPAS.config.continuousEvaluationForm.statusList;
        },

        userId() {
            return $MAPAS.config.continuousEvaluationForm.userId;
        },

        evaluationData() {
            const currentEvalConfig = $MAPAS.config.continuousEvaluationForm.currentEvaluation;
            return {
                data: currentEvalConfig?.evaluationData || {}
            };
        },

        currentEvaluation() {
            const currentEvalConfig = $MAPAS.config.continuousEvaluationForm.currentEvaluation;
            if (!currentEvalConfig) {
                return null;
            }
            
            const api = new API('registrationevaluation');
            const evaluation = api.getEntityInstance(currentEvalConfig.id);
            evaluation.populate(currentEvalConfig);
            return evaluation;
        },

        hasChatThread () {
            return $MAPAS.config.continuousEvaluationForm.hasChatThread;
        },

        isAwaitingMessage () {
            if (!this.hasChatThread) {
                return false;
            }

            return this.global.threads[this.threadId]?.user == $MAPAS.userId;
        },

        needsTiebreaker() {
            return $MAPAS.config.continuousEvaluationForm.needsTieBreaker;
        },

        isMinervaGroup() {
            return $MAPAS.config.continuousEvaluationForm.isMinervaGroup;
        },

        enableExternalReviews() {
            return $MAPAS.config.continuousEvaluationForm.showExternalReviews;
        },

        evaluationName() {
            return $MAPAS.config.continuousEvaluationForm.evaluationMethodName;
        },
    },

    methods: {
        handleOptionChange(selectedOption) {
            this.formData.data.status = selectedOption.value;
        },

        processResponse(data) {
            if (data.detail.response.status > 0) {
                this.isEditable = false;
            }

            if (data.detail.response.status == 0) {
                this.isEditable = true;
            }
        },

        handleCurrentEvaluationForm() {
            if (!this.currentEvaluation) {
                this.isEditable = this.editable;
                return;
            }
            
            return this.currentEvaluation.status > 0 ? this.isEditable = false : this.isEditable = this.editable;
        },

        removeEvaluationAttachment(file) {
            if (this.currentEvaluation && this.currentEvaluation.files) {
                this.currentEvaluation.files.evaluationAttachment = undefined;
            }
        },

        statusToString(status) {
            let result = '';
            this.statusList.forEach((item) => {
                if (item.value == status) {
                    result = item.label;
                }
            });

            return result;
        },

        validateErrors() {
            const messages = useMessages();
            let error = false;
            const global = useGlobalState();

            Object.keys(this.formData.data).forEach(key => {
                if (!this.formData.data[key] || this.formData.data[key] === '') {
                    messages.error(this.text('emptyField') + ' ' + this.dictFields(key) + ' ' + this.text('required'));
                    error = true;
                }
            });

            global.validateEvaluationErrors = error;
            return error;
        },

        dictFields(field) {
            const fields = {
                status: this.text('field_status_name'),
                obs: this.text('field_obs_name'),
                uid: this.text('field_uid_name'),
            };

            return fields[field];
        },


        skeleton() {
            return {
                data: {
                    uid: this.userId,
                    status: null,
                    obs: null,
                }
            };
        },
    },
});
