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

        formData: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            isEditable: true,
            evaluationId: null,
        };
    },

    created() {
        this.formData.sectionStatus = {};
        this.formData.obs = '';
        this.formData.data = {};
        this.formData.reason = {};
        
        this.isEditable = this.status > 0 ? false : this.editable;
        this.initializedCriteriaData();

        const global = useGlobalState();
        global.validateEvaluationErrors = this.validateErrors;
    },
    
    mounted() {
        window.addEventListener('responseEvaluation', this.processResponse);

        this.updateSectionStatusByFromData();
    },

    updated() {
        this.$nextTick(() => {
            const labels = this.$refs.formRoot?.querySelectorAll('.qualification-evaluation-form__criterion-options-reasons-label');
            
            if (!labels || labels.length === 0) return;
    
            labels.forEach(label => {
                const input = label.querySelector('input');
                if (input) {
                    if (input.value.length > 20) {
                        label.setAttribute('data-long-content', 'true');
                    } else {
                        label.removeAttribute('data-long-content');
                    }
    
                    input.addEventListener('input', () => {
                        if (input.value.length > 20) {
                            label.setAttribute('data-long-content', 'true');
                        } else {
                            label.removeAttribute('data-long-content');
                        }
                    });
                }
            });
        });
    },

    computed: {
        sections() {
            return $MAPAS.config.qualificationEvaluationForm.sections || [];
        },
        status() {
            return $MAPAS.config.qualificationEvaluationForm.evaluationData?.status || 0;
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
        needsTiebreaker() {
            return $MAPAS.config.qualificationEvaluationForm.needsTieBreaker;
        },
        isMinervaGroup() {
            return $MAPAS.config.qualificationEvaluationForm.isMinervaGroup;
        },
        enableExternalReviews() {
            return $MAPAS.config.qualificationEvaluationForm.showExternalReviews;
        },
        evaluationName() {
            return $MAPAS.config.qualificationEvaluationForm.evaluationMethodName;
        },
        consolidatedResult() {
            return this.consolidated();
        }
    },

    methods: {
        updateSectionStatus(sectionId, criteriaId, event) {
            let value = event.target.value;
            this.formData.data[criteriaId] = [value];
            
            this.formData.sectionStatus[sectionId] = this.sectionStatus(sectionId);

            this.consolidated();
        },

        toggleOthersOption(criteriaId, event) {
            const isChecked = event.target.checked;
    
            if (!this.formData.data[criteriaId]) {
                this.formData.data[criteriaId] = [];
            }
    
            if (isChecked) {
                if (!this.formData.data[criteriaId].includes('others')) {
                    this.formData.data[criteriaId].push('others');
                }
            } else {
                const index = this.formData.data[criteriaId].indexOf('others');
                if (index > -1) {
                    this.formData.data[criteriaId].splice(index, 1);
                }
                this.formData.data[`${criteriaId}_reason`] = ''; 
            }

            this.consolidated();
        },

        updateOption(critId, option) {
            const selectedOptions = this.formData.data[critId] || [];
    
            if (selectedOptions.includes(option)) {
                this.formData.data[critId] = selectedOptions.filter(opt => opt !== option);
            } else {
                this.formData.data[critId].push(option);
            }
        },

        showSectionAndCriterion(type) {
            return (
                !type.ranges.length || !type.categories.length || !type.proponentTypes.length ||      
                type.ranges.some(range => range === this.entity.range) ||
                type.categories.includes(this.entity.category) || 
                type.proponentTypes.includes(this.entity.proponentType)
            );
        },

        consolidated (){
            let result = true;
            for (let section of this.sections) {
                if(this.sectionStatus(section.id) === this.text('Não atende')){
                    result = false;
                    break;  
                }
            }
            return result ? this.text('Atende') : this.text('Não atende');
        },
        sectionStatus(sectionId){
            const section = this.sections.find(sec => sec.id === sectionId);

            if (!section) return;

            let nonEliminatoryCount = 0;
            let eliminatoryCrit = false;

            section.criteria.forEach(crit => {
                const critValue = this.formData.data[crit.id];
                if (!Array.isArray(critValue)) return;

                if (crit.nonEliminatory === 'false' && critValue.includes('invalid')) {
                    eliminatoryCrit = true;
                } else if (crit.nonEliminatory === 'true' && critValue.includes('invalid')) {
                    nonEliminatoryCount++;
                }
            });

            let newStatus;
            if (eliminatoryCrit || nonEliminatoryCount > section.numberMaxNonEliminatory) {
                newStatus = this.text('Não atende');
            } else {
                newStatus = this.text('Atende');
            }

            return newStatus;
        },
        updateSectionStatusByFromData() {
            const updatedSectionStatus = {};
    
            this.sections.forEach(section => {
                const criteriaEnabled = section.criteria.every(crit => Array.isArray(this.formData.data[crit.id]) && this.formData.data[crit.id].every(value => value === this.text('Atende')));
    
                const newStatus = criteriaEnabled ? this.text('Atende') : this.text('Não atende');
                updatedSectionStatus[section.id] = newStatus;
            });
    
            this.formData.sectionStatus = updatedSectionStatus;
            this.consolidated();
        },

        validateErrors() {
            let isValid = false;
            this.errors = [];

            for (let sectionIndex in this.sections) {
                let section = this.sections[sectionIndex];

                if (this.showSectionAndCriterion(section)) {
                    for (let crit of section.criteria) {
                        if (this.showSectionAndCriterion(crit)) {
                            let sectionName = section.name;
                            let value = this.formData.data[crit.id];
                            if (value.length <= 0) {
                                this.messages.error(`${this.text('Na seção')} ${sectionName}, ${this.text('O campo')} ${crit.name} ${this.text('é obrigatório')}`);
                                isValid = true;
                            }
                        }
                    }
                }

                let parecerValue = this.formData.data[section.id];
                if (!parecerValue || parecerValue === "") {
                    this.messages.error(this.text('O campo Parecer é obrigatório.'));
                    isValid = true;
                }
            }

            if (!this.formData.data.obs || this.formData.data.obs === "") {
                this.messages.error(this.text('O campo Observação é obrigatório.'));
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
        },

        initializedCriteriaData() {
            this.sections.forEach(section => {
                section.criteria.forEach(crit => {
                    if(!this.formData.data[crit.id]) {
                        this.formData.data[crit.id] = this.evaluationData[crit.id] ?? [];
                        this.formData.data[crit.id + '_reason'] = this.evaluationData[crit.id + '_reason'] ?? '';
                    }
                });

                this.formData.data[section.id] = this.evaluationData[section.id] ?? '';
            });

            this.formData.data['obs'] = this.evaluationData['obs'] ?? '';
        },
    },
});
