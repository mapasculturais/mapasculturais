app.component('simple-evaluation-form', {
    template: $TEMPLATES['simple-evaluation-form'],

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
        const text = Utils.getTexts('simple-evaluation-form')
        return { text }
    },

    data() {
        return {
            formData: {},
            isEditable: true,
        };
    },

    created() {
        const formData = this.evaluationData || this.skeleton();

        for(let key in formData) {
            this.formData[key] = formData[key];
        }
        
        this.handleCurrentEvaluationForm();

        const global = useGlobalState();
        global.validateEvaluationErrors = this.validateErrors;
    },

    mounted() {
        window.addEventListener('responseEvaluation', this.processResponse);
    },

    computed: {
        statusList() {
            return $MAPAS.config.simpleEvaluationForm.statusList;
        },

        userId() {
            return $MAPAS.config.simpleEvaluationForm.userId;
        },

        evaluationData() {
            return $MAPAS.config.simpleEvaluationForm.currentEvaluation?.evaluationData;
        },

        currentEvaluation() {
            return $MAPAS.config.simpleEvaluationForm.currentEvaluation;
        },

        needsTiebreaker() {
            return $MAPAS.config.simpleEvaluationForm.needsTieBreaker;
        },

        isMinervaGroup() {
            return $MAPAS.config.simpleEvaluationForm.isMinervaGroup;
        },

        enableExternalReviews() {
            return $MAPAS.config.simpleEvaluationForm.showExternalReviews;
        },

        evaluationName() {
            return $MAPAS.config.simpleEvaluationForm.evaluationMethodName;
        }
    },

    methods: {
        handleOptionChange(selectedOption) {
            this.formData.status = selectedOption.value;
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
            Object.keys(this.formData).forEach(key => { 
                if (!this.formData[key] || this.formData[key] === '') {
                    messages.error(this.text('emptyField') + ' ' + this.dictFields(key) + ' ' + this.text('required'));
                    error = true;
                }
            });
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
                uid: this.userId,
                status: null,
                obs: null,
            };
        },
    },
});
