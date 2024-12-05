
app.component('qualification-evaluation-config', {
    template: $TEMPLATES['qualification-evaluation-config'],

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const messages = useMessages();
        const text = Utils.getTexts("qualification-evaluation-config");
        return { text, messages };
      },

    data() {
        return {
            editingSections: [],
            autoSaveTimeOut:  null,
            optionsText: '',
            options: null,
            showFilters: false,
            maxNonEliminatory: false,
            excludeDistributionField: true,
        }
    },

    computed: {
        fieldsDict() {
            return $MAPAS.config.qualificationAssessmentSection.fieldsDict;
        },
    },

    methods: {
        generateUniqueNumber() {
            return Date.now() + Math.floor(Math.random() * 1000);
        },

        addSection() {
            if (!this.validateErrors(false,true)) {
                let sectionId = 's-' + this.generateUniqueNumber();
        
                if (!this.entity.sections) {
                    this.entity.sections = [];
                }
        
                this.entity.sections.push({
                    id: sectionId,
                    name: '',
                });
                
                this.editingSections[sectionId] = true;
              
                this.$nextTick(() => {
                    const sectionInputs = this.$refs['sectionNameInput']; 
                    const lastInput = sectionInputs[sectionInputs.length - 1]; 
                    if (lastInput) {
                        lastInput.focus();
                    }
                });
            } 
        },

        addCriteria(sid) {
            if (!this.validateErrors(true)) {
                let sectionId = sid;

                if (!this.entity.criteria) {
                    this.entity.criteria = [];
                }

                this.entity.criteria.push({
                    id: 'c-' + this.generateUniqueNumber(),
                    sid: sectionId,
                    name: '',
                    notApplyOption: 'false',
                    otherReasonsOption: 'false',
                    nonEliminatory: 'false',
                    weight: 1
                });


                this.$nextTick(() => {
                    const criteriaInputs = this.$refs['criteriaNameInput'];
                    const lastInput = criteriaInputs[criteriaInputs.length - 1];
                    if (lastInput) {
                        lastInput.focus();
                    }
                });
            }
        },
    
        editSections(sectionId) {
            this.editingSections[sectionId] = !this.editingSections[sectionId];
        },

        delSection(sectionId) {
            if(this.entity.criteria) {
                const criterias = this.entity.criteria.filter(criteria => criteria.sid !== sectionId);
                this.entity.criteria = criterias;
            }
            
            this.entity.sections = this.entity.sections.filter(section => section.id !== sectionId);
            this.save();
        },

        delCriteria(criteriaId) {
            this.entity.criteria = this.entity.criteria.filter(criteria => criteria.id !== criteriaId);
            this.save();
        },

        setSectionName() {
            this.save();
        },

        save(time = 100) {
            clearTimeout(this.autoSaveTimeOut)
            this.autoSaveTimeOut = setTimeout(() => {
                if(!this.validateErrors()) {
                    this.entity.save()
                }
            }, time);
        },

        validateErrors(addCriteria = false, addSection = false) {
            let hasError = false;

            let sections = this.entity.sections || [];
            let criteria = this.entity.criteria || [];

            sections.forEach((section) => {
                Object.keys(this.fieldsDict.sections).forEach((field) => {
                    let _field = this.fieldsDict.sections[field];
                    if (_field.isRequired && !section[field]) {
                        this.messages.error(`${this.text('theField')} ${this.text(_field.label)} ${this.text('isRequired')}`)
                        hasError = true;
                    }
                })

                if (addSection) {
                    if (!criteria.some(criterion => criterion.sid === section.id)) {
                        this.messages.error(`${this.text('theField')} ${this.text('fieldCriterionName')} ${this.text('isRequired')}`);
                        hasError = true;
                    }
                }

                if (!addCriteria && criteria && !criteria.some(criterion => criterion.sid === section.id)) {
                    hasError = true;
                }
            })

            if(criteria) {
                criteria.forEach((criterion) => {
                    Object.keys(this.fieldsDict.criteria).forEach((field) => {
                        let _field = this.fieldsDict.criteria[field];
                        if (_field.isRequired && !criterion[field]) {
                            let message = `${this.text('theField')} ${this.text(_field.label)} ${this.text('isRequired')} `;
                            
                            if(addCriteria) {
                                message = message + this.text('lastCriterion');
                            }
                            this.messages.error(message)
                            hasError = true;
                        }
                    })
                })
            }

            return hasError;
        },

        updateOptions(criteria) {
            if (!criteria.options) {
                criteria.options = [];
            }
            criteria.options.push(this.options);
            this.clear();
            this.save();
        },

        clear () {
            this.options = null;
        },

        enabledButton() {
            let value = this.options;
            
            if(value && value.trim()) {
                return true;
            }

            return false;
        },

        notApplyChange(criteria) {
            criteria.notApplyOption = criteria.notApplyOption ? 'true' : 'false';
            this.save();
        },

        otherReasonsChange(criteria) {
            criteria.otherReasonsOption = criteria.otherReasonsOption ? 'true' : 'false';
            this.save();
        },

        nonEliminatoryChange(criteria) {
            criteria.nonEliminatory = criteria.nonEliminatory ? 'true' : 'false';
            this.save();
        }, 

        enableMaxNonEliminatory(value, section) {
            if (value) {
                this.maxNonEliminatory = true;
                if (!section.maxNonEliminatory) {
                    section.maxNonEliminatory = true;
                }
            } else {
                this.maxNonEliminatory = false;
                if (section.maxNonEliminatory) {
                    section.maxNonEliminatory = false;
                    section.numberMaxNonEliminatory = 0; 
                }
            }
            this.save();
        }, 

        enableRequiredSectionObservation(value, section) {
            if (value) {
                if (!section.requiredSectionObservation) {
                    section.requiredSectionObservation = true;
                }
            } else {
                if (section.requiredSectionObservation) {
                    section.requiredSectionObservation = false;
                }
            }
            this.save();
        }, 
        
        enableFilterConfigSection(value, section) {
            if (value) {
                this.showFilters = true;
                if (!section.showFilters) {
                    section.showFilters = true;
                }
            } else {
                this.showFilters = false;
                if (section.showFilters) {
                    section.showFilters = false;

                    if (section.categories) section.categories = [];
                    if (section.ranges) section.ranges = [];
                    if (section.proponentTypes) section.proponentTypes = [];
        
                    this.entity.criteria.forEach(criteria => {
                        if (criteria.sid === section.id) { 
                            criteria.showFilters = false;
                            if (criteria.categories) criteria.categories = [];
                            if (criteria.ranges) criteria.ranges = [];
                            if (criteria.proponentTypes) criteria.proponentTypes = [];
                        }
                    });
                }
            }

            this.save();
        }, 

        enableFilterConfigCriteria(value, criteria) {
            if (value) {
                this.showFilters = true;
                if (!criteria.showFilters) {
                    criteria.showFilters = true;
                }
            } else {
                this.showFilters = false;
                if (criteria.showFilters) {
                    criteria.showFilters = false;
                    if (criteria.categories) criteria.categories = [];
                    if (criteria.ranges) criteria.ranges = [];
                    if (criteria.proponentTypes) criteria.proponentTypes = [];
                }
            }

            this.save();
        },

    },
});
