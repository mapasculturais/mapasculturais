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
        };
    },

    watch: {
        evaluationData: {
            handler(newValue) {
                if (newValue && this.fieldId) {
                    if (!this.formData.data[this.fieldId]) {
                        this.formData.data[this.fieldId] = {};
                    }
                    
                    this.formData.data[this.fieldId].obsItems = newValue.obs_items || '';
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
        userId() {
            return $MAPAS.userId;
        }
    },

    methods: {
        getDocumentaryData(data) {
            this.fieldName = data.detail.fieldName;
            this.enableForm = data.detail.type === 'evaluationForm.openForm';
            this.fieldId = data.detail.fieldId;

            if (this.enableForm) {
                this.formData.data[this.fieldId] = {
                    label: $DESCRIPTIONS.registration[this.fieldName]?.label || '',
                    obsItems: '',
                    obs: '',
                    evaluation: ''
                };
            }
        },

        validateErrors() {
            //let isValid = false;
            let isValid = true;
            return isValid;
        }
    },

    mounted() {
        window.addEventListener('documentaryData', this.getDocumentaryData);
    }
});
