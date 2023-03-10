app.component('opportunity-phase-list-results' , {
    template: $TEMPLATES['opportunity-phase-list-results'],

    setup() {
        const text = Utils.getTexts('opportunity-phase-list-results');
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

    methods: {
    }
});