app.component('claim-support', {
    template: $TEMPLATES['claim-support'],

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
