app.component('evaluation-form', {
    template: $TEMPLATES['evaluation-form'],

    props: {
        entity: {
            type: [Entity, Object],
            required: true,
        },
    },

    mounted() {
        window.addEventListener('resize', () => this.resizeForm());
        this.resizeForm();
        this.markSealValidatorFields();
    },

    updated() {
        this.resizeForm();
    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        const text = Utils.getTexts('evaluation-form');
        return { text, hasSlot }
    },

    data() {
        const global = useGlobalState();
        const sealValidators = $MAPAS.config.evaluationFormSealValidators || {};
        return {
            formData: {},
            validateErrors: global.validateEvaluationErrors,
            sealValidatorFields: sealValidators.enabled ? (sealValidators.fields || []) : [],
        }
    },

    computed: {
        hasSealValidatorFields() {
            return this.sealValidatorFields.length > 0;
        },
    },

    methods: {
        resizeForm() {
            const buttonsHeight = this.$refs.buttons.offsetHeight;
            const headerHeight = this.$refs.header.offsetHeight;
            const height = Math.max(window.innerHeight - buttonsHeight - headerHeight - 200, 500); 
            this.$refs.form.style.height = height + 'px';
        },

        markSealValidatorFields() {
            if (!this.hasSealValidatorFields) {
                return;
            }

            const message = {
                type: 'evaluationRegistration.setSealValidatorFields',
                fields: this.sealValidatorFields.map((field) => ({
                    fieldId: field.fieldId,
                    isValidator: true,
                    hasInvalidator: field.hasInvalidator,
                })),
            };

            [200, 700, 1500].forEach((delay) => {
                setTimeout(() => window.postMessage(message, '*'), delay);
            });
        }
    }
});
