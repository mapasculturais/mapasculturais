app.component('opportunity-claim-form', {
    template: $TEMPLATES['opportunity-claim-form'],
    setup() { },

    props: {
        entity: {
            type: Entity,
            required: true
        },

    },

    data() {
        return {
            claim: {},
        }
    },

    computed: {

        modalTitle() {
            return 'Solicitar Recurso';

        },
    },
});

