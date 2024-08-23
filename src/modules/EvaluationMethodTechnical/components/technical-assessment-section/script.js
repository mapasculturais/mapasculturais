
app.component('technical-assessment-section', {
    template: $TEMPLATES['technical-assessment-section'],

    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('technical-assessment-section');
        return { text, messages };
    },

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const messages = useMessages();
        const text = Utils.getTexts("technical-assessment-section");
        return { text, messages };
      },

    data() {
        return {
            editingSections: [],
            autoSaveTimeOut:  null,
        }
    },

    computed: {
        maxScore() {
            let totalScore = 0;

            if(this.entity.criteria && this.entity.criteria.length > 0) {
                this.entity.criteria.forEach(criteria => {
                    totalScore += criteria.max * criteria.weight;
                });
            }

            return totalScore;
        },
        fieldsDict() {
            return $MAPAS.config.technicalAssessmentsection.fieldsDict;
        }
    },

    methods: {
        generateUniqueNumber() {
            return Date.now() + Math.floor(Math.random() * 1000);
        },
        addSection() {
            let sectionId = 's-'+this.generateUniqueNumber();

            if(!this.entity.sections) {
                this.entity.sections = [];
            }

            this.entity.sections.push(
                {
                    id: sectionId,
                    name: ''
                }
            );
            this.editingSections[sectionId] = true;
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
                    title: '',
                    min: 0,
                    max: null,
                    weight: 1
                });


                this.$nextTick(() => {
                    const criteriaInputs = this.$refs['criteriaTitleInput'];
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
        change() {
            this.save(1000);
        },
        save(time = 100) {
            clearTimeout(this.autoSaveTimeOut)
            this.autoSaveTimeOut = setTimeout(() => {
                if(!this.validateErrors()) {
                    this.entity.save()
                }
            }, time);
        },
        validateErrors(addCriteria = false) {
            let hasError = false;

            this.entity.sections.forEach((section) => {
                Object.keys(this.fieldsDict.sections).forEach((field) => {
                    let _field = this.fieldsDict.sections[field];
                    if (_field.isRequired && !section[field]) {
                        this.messages.error(`${this.text('theField')} ${this.text(_field.label)} ${this.text('isRequired')}`)
                        hasError = true;
                    }
                })
            })

            if(this.entity.criteria) {
                this.entity.criteria.forEach((criterion) => {
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
        }
    }
});
