app.component('technical-evaluation-form', {
    template: $TEMPLATES['technical-evaluation-form'],

    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('technical-evaluation-form');
        return { text, messages }
    },

    props: {
        entity: {
            type: Object,
            required: true
        },
    },

    data() {
        const sections = $MAPAS.config.technicalEvaluationForm.sections;
        return {
            obs: '',
            viability: null,
            sections,
            resultNotes: '',
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
                        result += parseInt(value);
                    }
                }
            }
            return result;
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
            const value = this.formData.data[criterionId];
            const max = this.sections[sectionIndex].criteria.find(criterion => criterion.id === criterionId).max;

            if (value > max) {
                this.messages.error(this.text('note-higher-configured'));
                this.formData.data[criterionId] = max;
            }
            if (value < 0) {
                this.formData.data[criterionId] = 0;
            }
        },

        subtotal(sectionIndex) {
            let subtotal = 0;
            const section = this.sections[sectionIndex];

            for (let criterion of section.criteria) {
                const value = this.formData.data[criterion.id];
                if (value !== null && value !== undefined) {
                    subtotal += parseInt(value);
                }
            }

            return subtotal;
        },
        async saveEvaluation() {
            const api = new API('registration');
            let url = api.createUrl('saveEvaluation', { id: this.entity.id });
            await api.POST(url, this.formData).then(res => res.json()).then(response => {
                this.sendEvaluation();
            });
        },

        async sendEvaluation() {
            const api = new API('registration');
            let url = api.createUrl('sendEvaluation', { id: this.entity.id });
            await api.POST(url, this.formData).then(res => res.json()).then(response => {
                this.messages.success(this.text('evaluation-successfully'));
            });
        },
    }
});
