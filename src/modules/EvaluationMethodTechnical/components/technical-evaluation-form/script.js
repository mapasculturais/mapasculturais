app.component('technical-evaluation-form', {
    template: $TEMPLATES['technical-evaluation-form'],

    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('technical-evaluation-form');
        return { text, messages };
    },

    props: {
        entity: {
            type: Object,
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

    created() {
        this.formData.data = this.evaluationData || this.skeleton();
        this.handleCurrentEvaluationForm();
    },

    mounted() {
        window.addEventListener('responseEvaluation', this.processResponse);

        window.addEventListener('processErrors', this.validateErrors);
    },

    data() {
        return {
            obs: '',
            viability: null,
            isEditable: true,
        };
    },

    computed: {
        enableViability() {
            return $MAPAS.config.technicalEvaluationForm.enableViability;
        },
        sections() {
            return $MAPAS.config.technicalEvaluationForm.sections;
        },
        userId() {
            return $MAPAS.userId;
        },
        evaluationData() {
            return $MAPAS.config.technicalEvaluationForm.currentEvaluation?.evaluationData;
        },
        currentEvaluation() {
            return $MAPAS.config.technicalEvaluationForm.currentEvaluation;
        },
        notesResult() {
            let result = 0;
            for (let sectionIndex in this.sections) {
                for (let criterion of this.sections[sectionIndex].criteria) {
                    const value = this.formData.data[criterion.id];
                    if (value !== null && value !== undefined) {
                        result += parseFloat(value);
                    }
                }
            }
            return parseFloat(result.toFixed(2));
        },
        totalMaxScore() {
            let total = 0;
            for (let sectionKey in this.sections) {
                const section = this.sections[sectionKey];
                if (section.criteria && Array.isArray(section.criteria)) {
                    total += section.criteria.reduce((acc, criterion) => parseFloat(acc) + parseFloat((criterion.max || 0)), 0);
                }
            }
            return total;
        }
    },

    methods: {
        handleInput(sectionIndex, criterionId) {
            let value = this.formData.data[criterionId];
            const max = this.sections[sectionIndex].criteria.find(criterion => criterion.id === criterionId).max;

            if (!value && value !== 0) {
                this.messages.error(this.text('mandatory-note'));
                return;
            }
        
            if (value > max) {
                this.messages.error(this.text('note-higher-configured'));
                this.formData.data[criterionId] = max;
            } else if (value < 0) {
                this.formData.data[criterionId] = 0;
            }
        },

        subtotal(sectionIndex) {
            let subtotal = 0;
            const section = this.sections[sectionIndex];
            for (let criterion of section.criteria) {
                const value = this.formData.data[criterion.id];
                if (value !== null && value !== undefined) {
                    subtotal += parseFloat(value);
                }
            }

            return parseFloat(subtotal.toFixed(2));
        },

        validateErrors() {
            let isValid = false;
            const global = useGlobalState();

            for (let sectionIndex in this.sections) {
                for (let crit of this.sections[sectionIndex].criteria) {
                    let sectionName = this.sections[sectionIndex].name;
                    let value = this.formData.data[crit.id];
                    
                    if (!value && value !== 0) {
                        this.messages.error(`${this.text('on_section')} ${sectionName}, ${this.text('the_field')} ${crit.title} ${this.text('is_required')}`);
                        isValid = true;
                    }
                }
            }
            
            if (!this.formData.data.obs) {
                this.messages.error(this.text('technical-mandatory'));
                isValid = true;
            }

            if (this.enabledViablity && !this.formData.data.viability) {
                this.messages.error(this.text('technical-checkViability'));
                isValid = true;
            }

            global.validateEvaluationErrors = isValid;
            
            return isValid;
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
        
        skeleton() {
            return {
                uid: this.userId,
            };
        },
    }
});
