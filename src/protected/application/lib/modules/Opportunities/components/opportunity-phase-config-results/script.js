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
        }
    },

    computed: {
    },

    methods: {
    }
});