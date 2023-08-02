app.component('opportunity-phase-status' , {
    template: $TEMPLATES['opportunity-phase-status'],

    setup() {
        const text = Utils.getTexts('opportunity-phase-status');
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
        },
        tab: {
            type: String,
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