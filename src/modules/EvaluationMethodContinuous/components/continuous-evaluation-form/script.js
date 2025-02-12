app.component('continuous-evaluation-form', {
    template: $TEMPLATES['continuous-evaluation-form'],

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

    setup() {
        const text = Utils.getTexts('continuous-evaluation-form')
        return { text }
    },

    data() {
        return {
            isEditable: true,
        };
    },

    created() {
        this.formData['data'] = {};
        const formData = this.evaluationData || this.skeleton();

        for (let key in formData.data) {
            this.formData.data[key] = formData.data[key];
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
            return {
                data: $MAPAS.config.continuousEvaluationForm.currentEvaluation?.evaluationData
            };
        },

        currentEvaluation() {
            return $MAPAS.config.continuousEvaluationForm.currentEvaluation;
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
        }
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
            return this.currentEvaluation?.status > 0 ? this.isEditable = false : this.isEditable = this.editable;
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
