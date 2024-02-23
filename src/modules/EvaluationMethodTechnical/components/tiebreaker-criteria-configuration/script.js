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
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('tiebreaker-criteria-configuration')
        return { text, hasSlot }
    },

    beforeCreate() {},
    created() {},

    beforeMount() {},
    mounted() {},

    beforeUpdate() {},
    updated() {},

    beforeUnmount() {},
    unmounted() {},

    data() {
        return {
            totalCriteria: 0,
            criteria: {},
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

        allCriteria() {
            return this.criteria;
        },

        countCriteria() {
            return this.totalCriteria;
        },
    },
    
    methods: {
        newCriterion() {
            this.totalCriteria++;
            this.criteria[this.totalCriteria] = {
                id: this.totalCriteria,
                name: __('critério', 'tiebreaker-criteria-configuration') + ' ' + this.totalCriteria,
            }
        },

        setCriterion(option, id) {
            const field = Object.values(this.fields).filter(field => field.id == option.value);
            this.criteria[id].selected = !!field.length ? field[0] : null;
            this.criteria[id].select = option.value;
        },

        unsetCriterion(id) {
            delete this.criteria[id];
            this.totalCriteria--;
            this.reorderCriteria();
        },

        reorderCriteria() {
            const newCriteria = {};
            let counter = 1;
            for (const criterion in this.criteria) {
                this.criteria[criterion].id = counter;
                this.criteria[criterion].name = __('critério', 'tiebreaker-criteria-configuration') + ' ' + counter;
                newCriteria[counter] = this.criteria[criterion];
                counter++;
            }
            this.criteria = newCriteria;
        },

        criterionHasOptions(criterion) {
            return (criterion.selected && (criterion.selected.fieldType == 'checkboxes' || criterion.selected.fieldType == 'select'));
        }
    },
});
