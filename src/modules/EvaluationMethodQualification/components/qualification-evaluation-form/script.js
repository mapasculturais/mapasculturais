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
                reason: {},
            },
            consolidatedResult: this.text('Habilitado'),
            isEditable: true,
            evaluationId: null,
        };
    },

    created() {
        this.isEditable = this.status > 0 ? false : this.editable;

        this.initializedCriteriaData();
    },
    
    mounted() {
        window.addEventListener('responseEvaluation', this.processResponse);

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
        updateSectionStatus(sectionId) {
            const section = this.sections.find(sec => sec.id === sectionId);

            if(section) {
                const criteriaEnabled = section.criteria.every(crit => Array.isArray(this.formData.data[crit.id]) && this.formData.data[crit.id].every(value => value === this.text('Habilitado')));
                const newStatus = criteriaEnabled ? this.text('Habilitado') : this.text('Inabilitado');
        
                this.formData.sectionStatus = {
                    ...this.formData.sectionStatus,
                    [sectionId]: newStatus
                };
            }
            this.consolidated();
        },

        showSectionAndCriterion(type) {
            return (
                !type.ranges.length || !type.categories.length || !type.proponentTypes.length ||      
                type.ranges.some(range => range.label === this.entity.range) ||
                type.categories.includes(this.entity.category) || 
                type.proponentTypes.includes(this.entity.proponentType)
            );
        },

        consolidated (){
            let totalSections = this.sections.length;
            let sectionsEvaluated = Object.values(this.formData.sectionStatus).length;
            
            if(sectionsEvaluated > 0 && sectionsEvaluated < totalSections){
                this.consolidatedResult = this.text('Inabilitado');
                return;
            }

            this.consolidatedResult = Object.values(this.formData.sectionStatus).includes(this.text('Inabilitado')) ? this.text('Inabilitado') :this.text('Habilitado');
        },
        sectionStatus(sectionId){
            return this.formData.sectionStatus[sectionId] ?? this.text('Inabilitado');
        },
        updateSectionStatusByFromData() {
            const updatedSectionStatus = {};
    
            this.sections.forEach(section => {
                const criteriaEnabled = section.criteria.every(crit => Array.isArray(this.formData.data[crit.id]) && this.formData.data[crit.id].every(value => value === this.text('Habilitado')));
    
                const newStatus = criteriaEnabled ? this.text('Habilitado') : this.text('Inabilitado');
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
        
        combinedOptions(crit) {
            let options = [];
           
            options.push(
                {label: this.text('Habilitado'), value: 'Habilitado'},
                {label: this.text('Inabilitado'), value: 'Inabilitado'},
            );
            
            if (crit.notApplyOption === 'true') {
                options.push(
                    {label: this.text('Não se aplica'), value: 'Não se aplica'}
                );
            }
            
            if (crit.otherReasonsOption === 'true') {
                options.push(
                    {label: this.text('Outras'), value: 'Outras'}
                );
            }

            crit.options.forEach(option => {
                options.push(
                    {label: option, value: option}
                );
            });

            return options;
        },
    },
});
