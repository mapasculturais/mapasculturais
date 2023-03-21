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

    methods: {
        addPublishRegistrations () {
            this.phase.POST('publishRegistrations');
        }
    }
});