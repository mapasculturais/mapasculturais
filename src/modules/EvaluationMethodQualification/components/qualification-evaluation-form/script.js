app.component('qualification-evaluation-form', {
    template: $TEMPLATES['qualification-evaluation-form'],

    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('qualification-evaluation-form');
        return { text, messages };
    },
    created() {
        this.formData['data'] = this.evaluationData || this.skeleton();
        this.handleCurrentEvaluationForm();
        this.formData.uid = this.userId;
    },

    props: {
        entity: {
            type: Entity,
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
            isEditable: true,
            evaluationId: null,
        };
    },
    mounted() {
        window.addEventListener('responseEvaluation', this.processResponse);
        this.isEditable = this.status > 0 ? false : this.editable;

        if(!this.isEditable) {
            this.updateSectionStatusByFromData();
        }
    },

    computed: {
        sections() {
            return $MAPAS.config.qualificationEvaluationForm.sections || [];
        },
        status() {
            return $MAPAS.config.qualificationEvaluationForm.evaluationData?.status || 0;
        },
        statusText() {
            const statusMap = {
                0: this.text('Not_sent'),
                1: this.text('In_progress'),
                2: this.text('Sent'),
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
        currentEvaluation() {
            return $MAPAS.config.qualificationEvaluationForm.currentEvaluation;
        },
    },

    methods: {
        updateSectionStatus(sectionId, criteriaId, event) {
            this.formData.data = {
                ...this.formData.data,
                [criteriaId]: event.value
            };

            const section = this.sections.find(sec => sec.id === sectionId);

            if(section) {
                const criteriaEnabled = section.criteria.every(crit => this.formData.data[crit.id] === 'Habilitado');
                const newStatus = criteriaEnabled ? this.text('Enabled') : this.text('Disabled');
        
                this.formData.sectionStatus = {
                    ...this.formData.sectionStatus,
                    [sectionId]: newStatus
                };
            }
        },
        sectionStatus(sectionId){
            return this.formData.sectionStatus[sectionId];
        },
        updateSectionStatusByFromData() {
            const updatedSectionStatus = {};
    
            this.sections.forEach(section => {
                const criteriaEnabled = section.criteria.every(crit => {
                    const status = this.formData.data[crit.id];
                    return status === 'Habilitado';
                });
    
                const newStatus = criteriaEnabled ? this.text('Enabled') : this.text('Disabled');
    
                updatedSectionStatus[section.id] = newStatus;
            });
    
            this.formData.sectionStatus = updatedSectionStatus;
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

            return isValid;
        },
        processResponse(data) {
            if (data.detail.response.status > 0) {
                this.isEditable = false;
            } else {
                this.isEditable = true;
            }
        },

        handleCurrentEvaluationForm() {
            this.isEditable = this.currentEvaluation?.status > 0 ? false : this.editable;
        },
        skeleton() {
            return {
                uid: this.userId,
            };
        }
    },
});
