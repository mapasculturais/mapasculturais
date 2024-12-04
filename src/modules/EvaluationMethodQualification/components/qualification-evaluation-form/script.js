app.component('qualification-evaluation-form', {
    template: $TEMPLATES['qualification-evaluation-form'],

    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('qualification-evaluation-form');
        return { text, messages };
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
            errors: [],
        };
    },

    created() {
        this.handleCurrentEvaluationForm();
        this.formData.uid = this.userId;
        this.formData.sectionStatus = {};
        this.formData.obs = '';
        this.formData.data = {};
        this.formData.reason = {};
        
        this.isEditable = this.status > 0 ? false : this.editable;
        this.initializedCriteriaData();
    },
    
    mounted() {
        window.addEventListener('responseEvaluation', this.processResponse);

        window.addEventListener('processErrors', this.validateErrors);

        this.updateSectionStatusByFromData();

        this.$nextTick(() => {
            this.applyLongContentAttribute();
        });
    },

    updated() {
        this.$nextTick(() => {
            this.applyLongContentAttribute();
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

            if (value === 'invalid') {
                this.formData.data[criteriaId + '_reason'] = '';
            }
            
            this.formData.sectionStatus[sectionId] = this.sectionStatus(sectionId);

            this.consolidated();
        },

        toggleOthersOption(criteriaId, event) {
            const isChecked = event.target.checked;
    
            if (!this.formData.data[criteriaId]) {
                this.formData.data[criteriaId] = [];
            }
    
            if (isChecked) {
                this.formData.data[criteriaId] = ['invalid', 'others'];
                this.formData.data[`${criteriaId}_reason`] = '';
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

            if (this.formData.data[critId].includes('others')) {
                const index = this.formData.data[critId].indexOf('others');
                this.formData.data[critId].splice(index, 1);
            }
    
            if (!selectedOptions.includes(option)) {
                this.formData.data[critId] = ['invalid', option];
            } else {
                this.formData.data[critId] = selectedOptions.filter(opt => opt !== option);
            }
        },

        showSectionAndCriterion(type) {
            if(type.categories.length > 0 && !type.categories.includes(this.entity.category)) {
                return false
            }

            if(type.proponentTypes.length > 0 && !type.proponentTypes.includes(this.entity.proponentType)) {
                return false
            }

            if(type.ranges.length > 0 && !type.ranges.includes(this.entity.range)) {
                return false
            }

            return true;
        },

        consolidated (){
            let allSectionsEvaluated = true;
            let hasNotMeetCriteria = false;

            for (const section of this.sections) {
                const sectionStatus = this.sectionStatus(section.id);

                if (sectionStatus === this.text('Avaliação incompleta')) {
                    allSectionsEvaluated = false; 
                }

                if (sectionStatus === this.text('Não atende')) {
                    hasNotMeetCriteria = true; 
                }
            }

            if (!allSectionsEvaluated) {
                return this.text('Avaliação incompleta'); 
            }

            if (hasNotMeetCriteria) {
                return this.text('Inabilitado'); 
            }

            return this.text('Habilitado'); 
        },
        sectionClass(sectionId) {
            const status = this.sectionStatus(sectionId);
            if (status === this.text('Não atende')) {
                return 'qualification-disabled';
            } else if (status === this.text('Avaliação incompleta')) {
                return 'qualification-incomplete';
            } else {
                return 'qualification-enabled';
            }
        },
        sectionStatus(sectionId){
            const section = this.sections.find(sec => sec.id === sectionId);

            if (!section) return;

            let nonEliminatoryCount = 0;

            for (const crit of section.criteria) {
                const critValue = this.formData.data[crit.id];
                if (!Array.isArray(critValue)) continue;
                if (!this.showSectionAndCriterion(crit)) continue;

                if (crit.nonEliminatory === 'false' && critValue.includes('invalid')) {
                    return this.text('Não atende');
                } else if (critValue.length === 0) {
                    return this.text('Avaliação incompleta');
                } else if (crit.nonEliminatory === 'true' && critValue.includes('invalid')) {
                    nonEliminatoryCount++;
                }
            }

            if (nonEliminatoryCount > section.numberMaxNonEliminatory && section.numberMaxNonEliminatory > 0) {
                return this.text('Não atende');
            } else {
                return this.text('Atende');
            }
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
            const global = useGlobalState();

            for (let sectionIndex in this.sections) {
                let section = this.sections[sectionIndex];
                if (!this.showSectionAndCriterion(section)) {
                    continue;
                }

                for (let crit of section.criteria) {
                    if (!this.showSectionAndCriterion(crit)) {
                        continue;
                    }
                   
                    let sectionName = section.name;
                    let value = this.formData.data[crit.id];
                    if (value.length <= 0) {
                        this.messages.error(`${this.text('Na seção')} ${sectionName}, ${this.text('O campo')} ${crit.name} ${this.text('é obrigatório')}`);
                        isValid = true;
                    }
                    
                    if (crit.nonEliminatory === 'false' && value.includes('invalid')) {
                        let hasRecommendation = false;

                        if (crit.options.length > 0) {
                            for (let option of crit.options) {
                                if (this.formData.data[crit.id]?.includes(option)) {
                                    hasRecommendation = true;
                                    break;
                                }
                            }
                        }

                        if (crit.otherReasonsOption && this.formData.data[crit.id]?.includes('others')) {
                            const reason = this.formData.data[crit.id + '_reason'];
                            if (reason && reason.trim() !== '') {
                                hasRecommendation = true;
                            }
                        }

                        if (!hasRecommendation) {
                            this.messages.error(`${this.text('Na seção')} ${sectionName}, ${this.text('Para o critério')} ${crit.name}, ${this.text('é necessário preencher ou selecionar uma recomendação para atender ao critério')}`);
                            isValid = true;
                        }
                    }
                }

                let parecerValue = this.formData.data[section.id];
                if (section?.requiredSectionObservation && (!parecerValue || parecerValue === "")) {
                    this.messages.error(this.text('O campo Observações/parecer é obrigatório.'));
                    isValid = true;
                }

            }

            global.validateEvaluationErrors = isValid;

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

        applyLongContentAttribute() {
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
    
                    if (!input.disabled) {
                        input.addEventListener('input', () => {
                            if (input.value.length > 20) {
                                label.setAttribute('data-long-content', 'true');
                            } else {
                                label.removeAttribute('data-long-content');
                            }
                        });
                    }
                }
            });
        },
    },
});
