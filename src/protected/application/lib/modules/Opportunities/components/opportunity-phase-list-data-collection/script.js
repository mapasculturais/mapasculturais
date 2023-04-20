app.component('opportunity-phase-list-data-collection' , {
    template: $TEMPLATES['opportunity-phase-list-data-collection'],

    setup() {
        const text = Utils.getTexts('opportunity-phase-list-data-collection');
        return { text };
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        phases: {
            type: Array,
            required: true
        }
    },

    computed: {
        index() {
            return this.phases.indexOf(this.entity);
        },

        previousPhase() {
            return this.phases[this.index - 1];
        },

        nextPhase() {
            return this.phases[this.index + 1];
        },
    }
});