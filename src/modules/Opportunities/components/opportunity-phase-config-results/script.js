app.component('opportunity-phase-config-results' , {
    template: $TEMPLATES['opportunity-phase-config-results'],

    setup() {
        const text = Utils.getTexts('opportunity-phase-config-results');
        return { text };
    },

    props: {
        phase: {
            type: Entity,
            required: true
        },
        phases: {
            type: Array,
            required: true
        },

        tab: {
            type: String,
        },
    },

    computed: {
        firstPhase() {
            return this.phases[0];
        },

        seals() {
            return $MAPAS.config?.opportunityPhaseConfigResults?.seals;
        },
    },

    methods: {
    }
});