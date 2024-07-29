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
            fieldName: '',
            fieldLabel: '',
            fieldId: null,
            obsItems: '',
            obs: '',
            evaluation: ''
        };
    },

    watch: {
        evaluationData: {
            handler(newValue) {
                if (newValue) {
                    this.obsItems = newValue.obs_items || '';
                    this.obs = newValue.obs || '';
                    this.fieldLabel = newValue.label || '';
                    this.evaluation = newValue.evaluation || '';
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
            //console.log('DATA', data);
            this.fieldName = data.detail.fieldName;
            this.enableForm = data.detail.type === 'evaluationForm.openForm';
            this.fieldId = data.detail.fieldId;
            
            if(this.enableForm) {
                this.fieldLabel = $DESCRIPTIONS.registration[this.fieldName].label
            }
        },
        superTeste(data) {
            if(data.data?.type !== 'resize') {
                console.log(data)
            }
        }
    },

    mounted() {
        window.addEventListener('documentaryData', this.getDocumentaryData);
        window.addEventListener('message', this.superTeste);
    }
});
