app.component('opportunity-phase-list-evaluation' , {
    template: $TEMPLATES['opportunity-phase-list-evaluation'],

    setup() {
        const text = Utils.getTexts('opportunity-phase-list-evaluation');
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