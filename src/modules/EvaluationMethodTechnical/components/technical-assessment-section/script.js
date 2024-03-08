
app.component('technical-assessment-section', {
    template: $TEMPLATES['technical-assessment-section'],

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },

    data() {
        return {
            editingSections: []
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
            let sectionId = sid;

            if(!this.entity.criteria) {
                this.entity.criteria = [];
            }

            this.entity.criteria.push({
                id: 'c-'+this.generateUniqueNumber(),
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
        },
        sendConfigs() {
            this.entity.save(3000);
        },
        editSections(sectionId) {
            this.editingSections[sectionId] = !this.editingSections[sectionId];
        },
        delSection(sectionId) {
            const criterias = this.entity.criteria.filter(criteria => criteria.sid !== sectionId);
            this.entity.criteria = criterias;
            this.entity.sections = this.entity.sections.filter(section => section.id !== sectionId);
            this.autoSave();
        },
        delCriteria(criteriaId) {
            this.entity.criteria = this.entity.criteria.filter(criteria => criteria.id !== criteriaId);
            this.autoSave();
        },
        autoSave() {
            this.entity.save(3000)
        }
    }
});
