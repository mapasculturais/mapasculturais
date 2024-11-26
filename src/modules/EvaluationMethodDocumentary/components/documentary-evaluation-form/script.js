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
        },

        formData: { 
            type: Object,
            required: true
        }
    },

    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('documentary-evaluation-form');

        return { text, messages };
    },

    created() {
        this.formData.data = {};
    },

    mounted() {
        window.addEventListener('evaluationRegistrationList', this.getEvaluationList);
        window.addEventListener('documentaryData', this.getDocumentaryData);
        window.addEventListener('responseEvaluation', this.processResponse);
        window.addEventListener('processErrors', this.validateErrors);

        this.isEditable = this.canEvaluate();
    },

    data() {
        return {
            enableForm: false,
            fieldName: '',
            fieldId: null,
            fieldType: null,
            userId: null,
            userName: '',
            isEditable: this.editable,
            evaluationData: $MAPAS.config.documentaryEvaluationForm.evaluationData?.evaluationData || {},
            newStatus: null
        };
    },

    computed: {
        status() {
            return $MAPAS.config.documentaryEvaluationForm.evaluationData?.status || 0;
        },

        needsTiebreaker() {
            return $MAPAS.config.documentaryEvaluationForm.needsTieBreaker;
        },

        isMinervaGroup() {
            return $MAPAS.config.documentaryEvaluationForm.isMinervaGroup;
        },

        enableExternalReviews() {
            return $MAPAS.config.documentaryEvaluationForm.showExternalReviews;
        },

        evaluationName() {
            return $MAPAS.config.documentaryEvaluationForm.evaluationMethodName;
        }
    },

    methods: {
        getDocumentaryData(data) {
            this.fieldType = data.detail.fieldType;
            this.fieldName = this.fieldType === 'file' ? data.detail.fieldName.replace('field_', 'rfc_') : data.detail.fieldName;
            this.enableForm = data.detail.type === 'evaluationForm.openForm';
            this.fieldId = data.detail.fieldId;
            
            if (this.enableForm) {
                this.getEvaluationData();

                this.formData.uid = this.userId;
                this.formData.data[this.fieldId] = {
                    label: $MAPAS.config.documentaryEvaluationForm.fieldsInfo[this.fieldName]?.label || '',
                    obsItems: this.evaluationData[this.fieldId]?.obsItems ?? '',
                    obs: this.evaluationData[this.fieldId]?.obs ?? '',
                    evaluation: this.evaluationData[this.fieldId]?.evaluation ?? '',
                };
            }
        },

        validateErrors() {
            let hasError = false;
            const global = useGlobalState();
            
            Object.values(this.formData.data).forEach(item => {
                if(this.newStatus && this.newStatus > 0) {
                    if(!item.obsItems) {
                        this.messages.error(this.text('o campo "Descumprimento do(s) item(s) do edital" não foi avaliado'));
                        hasError = true;
                    }
    
                    if(!item.obs) {
                        this.messages.error(this.text('o campo "Justificativa / Observações" não foi avaliado'));
                        hasError = true;
                    }
                }
            });

            global.validateEvaluationErrors = hasError;

            return hasError;
        },

        getEvaluationList(data) {
            let evaluationRegistrationList = data.detail.evaluationRegistrationList ?? null;
            if (evaluationRegistrationList) {
                evaluationRegistrationList.forEach(item => {
                    if (item.valuer && item.valuer.user === $MAPAS.userId) {
                        this.userId = item.valuer.user;
                        this.userName = item.valuer.name;
                    }
                });
            }
        },

        processResponse(data) {
            this.newStatus = data.detail.response.status;
            this.isEditable = this.newStatus >= 1 ? false : true;
        },

        setEvaluationData(fieldId, status = null) {
            this.evaluationData[fieldId] = this.formData.data[fieldId];

            if(status) {
                let className = `evaluation-${status}`;

                window.parent.postMessage({
                    type: 'evaluationRegistration.setClass',
                    className: className,
                    fieldId: fieldId
                });
            }
        },

        getEvaluationData() {
            const data = $MAPAS.config?.documentaryEvaluationForm?.evaluationData?.evaluationData;
            
            if((data && this.fieldId && !this.isEditable) || (data && Object.values(this.evaluationData).length == 0)) {
                return this.evaluationData[this.fieldId] = data[this.fieldId];
            }

            return {};
        },

        canEvaluate() {
            if(!this.entity.currentUserPermissions['evaluate']) {
                return false;
            }
    
            if(this.status >= 1) {
                return false;
            }
    
            return true;
        }
    },
});
