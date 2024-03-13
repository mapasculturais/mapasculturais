app.component('tiebreaker-criteria-configuration', {
    template: $TEMPLATES['tiebreaker-criteria-configuration'],

    props: {
        phase: {
            type: Entity,
            required: true,
        },
    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        const text = Utils.getTexts('tiebreaker-criteria-configuration')
        return { text, hasSlot }
    },
    
    updated () {
        this.save();
    },

    data() {
        const config = this.phase.tiebreakerCriteriaConfiguration || {};
        let totalCriteria = Object.keys(config).length;
        let criteria = Object.assign({}, config);
        let isActive = !!Object.keys(criteria).length;

        return {
            isActive,
            totalCriteria,
            criteria,
        }
    },

    computed: {
        sections() {
            let sections = this.phase.sections.map((section) => {
                const all_criteria = this.phase.criteria;
                section.criteria = [];
                Object.values(all_criteria).forEach(criterion => {
                    if (criterion.sid == section.id) {
                        section.criteria.push(criterion);
                    }
                });
                return section;
            });

            return sections;
        },

        fields() {
            return $MAPAS.config.tiebreakerCriteriaConfiguration.fields[this.phase.opportunity._id];
        },
    },
    
    methods: {
        open() {
            this.isActive = true;
        },

        close() {
            this.isActive = false;
        },

        newCriterion() {     
            this.criteria[this.totalCriteria+1] = {
                id: this.totalCriteria+1,
                name: __('critério', 'tiebreaker-criteria-configuration') + ' ' + (this.totalCriteria+1),
            };
            this.totalCriteria++;
        },

        setCriterion(option, id) {
            const field = Object.values(this.fields).filter(field => field.fieldName == option.value);
            this.criteria[id].selected = !!field.length ? field[0] : null;
            this.criteria[id].criterionType = option.value;
            this.criteria[id].preferences = this.checkCriterionType(this.criteria[id], ['checkboxes', 'select']) ? [] : null;
        },

        unsetCriterion(id) {
            let counter = 1;
            for (const criterion in this.criteria) {
                if (criterion == id) {
                    delete this.criteria[criterion];
                }
                counter++;
            }
            this.reorderCriteria();
        },

        reorderCriteria() {
            let counter = 1;
            let newCriteria = {};
            for (const criterion in this.criteria) {
                this.criteria[criterion].id = counter;
                this.criteria[criterion].name = __('critério', 'tiebreaker-criteria-configuration') + ' ' + counter;
                newCriteria[counter] = this.criteria[criterion];
                counter++;
            }
            
            this.criteria = newCriteria;
            this.totalCriteria = Object.keys(this.criteria).length ?? 0;

            if (!!!this.totalCriteria) {
                this.isActive = false;
            }
        },

        checkCriterionType(criterion, allowedTypes = []) {
            return criterion.selected ? !!allowedTypes.includes(criterion.selected.fieldType) : false 
        },

        async save() {
            this.phase.tiebreakerCriteriaConfiguration = this.criteria;
            await this.phase.save(3000);
        }
    },
});
