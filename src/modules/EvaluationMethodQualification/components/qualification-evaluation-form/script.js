app.component('qualification-evaluation-form', {
    template: $TEMPLATES['qualification-evaluation-form'],

    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('qualification-evaluation-form');
        return { text, messages };
    },
    created() {
        this.formData['data'] = this.evaluationData || this.skeleton();
        this.formData.uid = this.userId;
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
    },

    data() {
        return {
            formData: {
                sectionStatus: {},
                obs: '',
                data: {},
            },
            evaluationId: null,
        };
    },
    
    computed: {
        sections() {
            return $MAPAS.config.qualificationEvaluationForm.sections || [];

        },
        status() {
            return $MAPAS.config.qualificationEvaluationForm.evaluationData || [];
        },
        statusText() {
            const statusMap = {
                0: 'Não Enviada',
                1: 'Em andamento',
                2: 'Enviada',
            };

            return statusMap[this.status];
        },
        evaluationData() {
            const evaluation = $MAPAS.config.qualificationEvaluationForm.evaluationData;
            return evaluation && evaluation.evaluationData ? evaluation.evaluationData : {};
        },
        userId() {
            return $MAPAS.userId;
        },
    },

    methods: {
        handleChange(sectionId) {
            this.formData.sectionStatus[sectionId] = 'Inabilitado';
        },
        validateErrors() {
            let isValid = false;
            this.errors = [];

            for (let sectionIndex in this.sections) {
                for (let crit of this.sections[sectionIndex].criteria) {
                    let sectionName = this.sections[sectionIndex].name;
                    let value = this.formData.data[crit.id];
                    if (!value || value === "") {
                        this.messages.error(`${this.text('on_section')} ${sectionName}, ${this.text('the_field')} ${crit.name} ${this.text('is_required')}`);
                        isValid = true;
                    }
                }
            }

            if (!this.formData.data.obs || this.formData.data.obs === "") {
                this.messages.error(this.text('technical-mandatory'));
                isValid = true;
            }

    }
});
