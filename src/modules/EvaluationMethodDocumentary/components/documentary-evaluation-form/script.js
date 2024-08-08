app.component('documentary-evaluation-form', {
    template: $TEMPLATES['documentary-evaluation-form'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        editable: {
            type: Boolean,
            default: true
        }
    },

    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('documentary-evaluation-form');

        return { text, messages };
    },

    data() {
        return {
            enableForm: false,
            formData: {
                data: {}
            },
            fieldName: '',
            fieldId: null,
            userId: null,
            userName: '',
            isEditable: this.editable
        };
    },

    watch: {
        evaluationData: {
            handler(newValue) {
                if (newValue && this.fieldId) {
                    if (!this.formData.data[this.fieldId]) {
                        this.formData.data[this.fieldId] = {};
                    }
                    
                    this.formData.data[this.fieldId].obsItems = newValue.obsItems || '';
                    this.formData.data[this.fieldId].obs = newValue.obs || '';
                    this.formData.data[this.fieldId].label = newValue.label || '';
                    this.formData.data[this.fieldId].evaluation = newValue.evaluation || '';
                }
            },
            deep: true,
            immediate: true
        }
    },

    computed: {
        evaluationData() {
            const data = $MAPAS.config?.documentaryEvaluationForm?.evaluationData?.evaluationData;
            if (data && this.fieldId) {
                return data[this.fieldId] || {};
            }
            return {};
        },
        status() {
            return $MAPAS.config.documentaryEvaluationForm.evaluationData?.status || 0;
        }
    },

    methods: {
        getDocumentaryData(data) {
            this.fieldName = data.detail.fieldName;
            this.enableForm = data.detail.type === 'evaluationForm.openForm';
            this.fieldId = data.detail.fieldId;

            if (this.enableForm) {
                this.formData.uid = this.userId;
                this.formData.data[this.fieldId] = {
                    label: $DESCRIPTIONS.registration[this.fieldName]?.label || '',
                    obsItems: '',
                    obs: '',
                    evaluation: '',
                };
            }
        },

        validateErrors() {
            let hasError = false;
            
            Object.values(this.formData.data).forEach(item => {
                if(!item.obsItems) {
                    this.messages.error(this.text('o campo "Descumprimento do(s) item(s) do edital" não foi avaliado'));
                    hasError = true;
                }

                if(!item.obs) {
                    this.messages.error(this.text('o campo "Justificativa / Observações" não foi avaliado'));
                    hasError = true;
                }
            });

            return hasError;
        },

        getEvaluationList(data) {
            let evaluationRegistrationList = data.detail.evaluationRegistrationList ?? null;

            if (evaluationRegistrationList) {
                evaluationRegistrationList.forEach(item => {
                    if (item.valuer) {
                        if (item.valuer.id === $MAPAS.userId) {
                            this.userId = item.valuer.id;
                            this.userName = item.valuer.name;
                        }
                    }
                });
            }
        },

        processResponse(data) {
            this.isEditable = data.detail.response.status > 0 ? false : true;
        }
    },

    mounted() {
        window.addEventListener('evaluationRegistrationList', this.getEvaluationList);
        window.addEventListener('documentaryData', this.getDocumentaryData);
        window.addEventListener('responseEvaluation', this.processResponse);

        this.isEditable = this.status > 0 ? false : this.editable;
    }
});
