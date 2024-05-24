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
    },

    mounted() {
        for (let sectionIndex in this.sections) {
            for (let criterion of this.sections[sectionIndex].criteria) {
                this.formData.data[criterion.id] = 0;
            }
        }
    },

    data() {
        const sections = $MAPAS.config.technicalEvaluationForm.sections;
        const enabledViablity = $MAPAS.config.technicalEvaluationForm.enableViability;
        return {
            obs: '',
            viability: null,
            enabledViablity,
            sections,
            formData: {
                uid: $MAPAS.userId,
                data: {},
            },
        };
    },

    computed: {
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
                    total += section.criteria.reduce((acc, criterion) => acc + (criterion.max || 0), 0);
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

        validaErrors() {
            let isValid = true;
            
            for (let sectionIndex in this.sections) {
                for (let criterion of this.sections[sectionIndex].criteria) {
                    let value = this.formData.data[criterion.id];
                    
                    if (!this.formData.data.obs) {
                        this.messages.error(this.text('technical-mandatory'));
                        isValid = false;
                    }
        
                    if (!this.viability && this.enabledViablity) {
                        this.messages.error(this.text('technical-checkViability'));
                        isValid = false;
                    }
                    
                    if (!value && value !== 0) {
                        this.messages.error(this.text('mandatory-note'));
                        isValid = false;
                    }
                }
            }
            
            return isValid;
        },
    }
});
