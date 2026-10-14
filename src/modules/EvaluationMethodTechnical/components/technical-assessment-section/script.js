
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
            scoreOptionInputs: {},
        }
    },

    computed: {
        hasEvaluationsStarted() {
            return this.entity.hasStartedEvaluations ?? $MAPAS.config.technicalAssessmentsection.hasEvaluationsStarted;
        },
        scoreMode() {
            return this.entity.scoreMode || 'typed';
        },
        isAdmin() {
            return $MAPAS.config.technicalAssessmentsection.isAdmin;
        },
        canDeleteCriteriaAndSections() {
            return $MAPAS.config.technicalAssessmentsection.canDeleteCriteriaAndSections;
        },
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
        parseScoreOptions(value) {
            const parts = `${value ?? ''}`.split(',').map(part => part.trim());
            if (parts.some(part => !/^\d+(?:\.\d+)?$/.test(part))) {
                return null;
            }
            const options = parts.map(Number);
            if (options.some(value => !Number.isFinite(value)) || new Set(options).size !== options.length) {
                return null;
            }
            return options.sort((a, b) => a - b);
        },
        scoreOptionsText(criterion) {
            return this.scoreOptionInputs[criterion.id] ?? (criterion.scoreOptions || []).join(',');
        },
        changeScoreMode(mode) {
            if (this.hasEvaluationsStarted || !['typed', 'fixed'].includes(mode)) {
                return;
            }
            this.entity.scoreMode = mode;
            this.save();
        },
        updateScoreOptions(criterion, value) {
            if (this.hasEvaluationsStarted) {
                return;
            }
            this.scoreOptionInputs[criterion.id] = value;
            const options = this.parseScoreOptions(value);
            if (!options) {
                return;
            }
            criterion.scoreOptions = options;
            criterion.min = options[0];
            criterion.max = options[options.length - 1];
            this.save(1500);
        },
        checkScoreOptions(criterion) {
            if (!this.parseScoreOptions(this.scoreOptionsText(criterion))) {
                this.messages.error(this.text('invalidScoreOptions'));
                return false;
            }
            return true;
        },
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
                    weight: 1,
                    scoreOptions: []
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
            if (!this.canDeleteCriteriaAndSections) {
                return;
            }

            if(this.entity.criteria) {
                const criterias = this.entity.criteria.filter(criteria => criteria.sid !== sectionId);
                this.entity.criteria = criterias;
            }
            
            this.entity.sections = this.entity.sections.filter(section => section.id !== sectionId);
            this.save();
        },
        delCriteria(criteriaId) {
            if (!this.canDeleteCriteriaAndSections) {
                return;
            }

            this.entity.criteria = this.entity.criteria.filter(criteria => criteria.id !== criteriaId);
            this.save();
        },
        change() {
            this.save(1000);
        },
        save(time = 100) {
            clearTimeout(this.autoSaveTimeOut)
            this.autoSaveTimeOut = setTimeout(() => {
                if(this.canAutosave()) {
                    this.entity.save()
                }
            }, time);
        },
        canAutosave() {
            const sections = Array.isArray(this.entity.sections) ? this.entity.sections : [];
            const criteria = Array.isArray(this.entity.criteria) ? this.entity.criteria : [];

            if (!sections.length && !criteria.length) {
                return true;
            }

            const validSectionIds = new Set();
            for (const section of sections) {
                if (!section?.id || !`${section.name ?? ''}`.trim()) {
                    return false;
                }
                validSectionIds.add(section.id);
            }

            const sectionsWithCriteria = new Set();
            for (const criterion of criteria) {
                if (!criterion?.sid || !validSectionIds.has(criterion.sid)) {
                    return false;
                }

                if (!`${criterion.title ?? ''}`.trim()) {
                    return false;
                }

                if (this.scoreMode === 'fixed' && !this.parseScoreOptions(this.scoreOptionsText(criterion))) {
                    return false;
                }

                if (criterion.max === null || criterion.max === undefined || criterion.max === '' || Number.isNaN(Number(criterion.max))) {
                    return false;
                }

                if (criterion.weight === null || criterion.weight === undefined || criterion.weight === '' || Number.isNaN(Number(criterion.weight))) {
                    return false;
                }

                sectionsWithCriteria.add(criterion.sid);
            }

            return sections.every((section) => sectionsWithCriteria.has(section.id));
        },
        validateErrors(addCriteria = false) {
            let hasError = false;

            if (this.entity.sections && Array.isArray(this.entity.sections)) {
                this.entity.sections.forEach((section) => {
                    Object.keys(this.fieldsDict.sections).forEach((field) => {
                        let _field = this.fieldsDict.sections[field];
                        if (_field.isRequired && !section[field]) {
                            this.messages.error(`${this.text('theSection')} ${this.text(_field.label)} ${this.text('isRequired')}`)
                            hasError = true;
                        }
                    })
                });
            }

            if(this.entity.criteria) {
                this.entity.criteria.forEach((criterion) => {
                    if (this.scoreMode === 'fixed' && !this.checkScoreOptions(criterion)) {
                        hasError = true;
                    }
                    Object.keys(this.fieldsDict.criteria).forEach((field) => {
                        let _field = this.fieldsDict.criteria[field];
                        if (_field.isRequired && (criterion[field] === null || criterion[field] === undefined || criterion[field] === '')) {
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

            if (!addCriteria && this.entity.sections && Array.isArray(this.entity.sections)) {
                const criteria = Array.isArray(this.entity.criteria) ? this.entity.criteria : [];

                this.entity.sections.forEach((section) => {
                    const hasCriteriaForSection = criteria.some(criterion => criterion.sid === section.id);

                    if (!hasCriteriaForSection) {
                        const sectionName = section.name || this.text('section');
                        this.messages.error(
                            `${this.text('theSection')} ${sectionName} ${this.text('must_have_at_least_one_criterion')}`
                        );
                        hasError = true;
                    }
                });
            }

            return hasError;
        }
    }
});
