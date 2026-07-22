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
        document.getElementById('evaluation-registration')?.addEventListener('load', () => this.markSealValidatorFields());
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
                    seals: this.getFieldValidatorSeals(field),
                })),
            };

            [200, 700, 1500, 3000, 5000].forEach((delay) => {
                setTimeout(() => this.postToEvaluationRegistration(message), delay);
            });
        },

        postToEvaluationRegistration(message) {
            window.postMessage(message, '*');
            document.getElementById('evaluation-registration')?.contentWindow?.postMessage(message, '*');
        },

        getFieldValidatorSeals(field) {
            const sealsById = {};
            const entitySeals = Array.isArray(this.entity.seals) ? this.entity.seals : Object.values(this.entity.seals || {});
            for (const seal of entitySeals) {
                sealsById[seal.sealId || seal.id] = seal;
            }

            return (field.validators || []).map((validator) => {
                const seal = sealsById[validator.sealId] || {};
                return {
                    sealId: validator.sealId,
                    fieldName: validator.fieldName,
                    name: seal.name || validator.sealName,
                    fieldStatus: validator.fieldStatus,
                    expiryDate: validator.expiryDate,
                    isInvalidator: validator.isInvalidator,
                    isUnlocked: validator.isUnlocked,
                    isLocked: validator.isLocked,
                    hasSealRelation: validator.hasSealRelation,
                    validateDate: validator.validateDate,
                    createTimestamp: seal.createTimestamp || validator.createTimestamp,
                    files: {
                        avatar: seal.files?.avatar || validator.files?.avatar,
                    },
                };
            });
        }
    }
});
