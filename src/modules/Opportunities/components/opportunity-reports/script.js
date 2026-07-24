app.component('opportunity-reports', {
    template: $TEMPLATES['opportunity-reports'],

    props: {
        entity: {
            type: Entity,
            required: true,
        },
    },

    data() {
        return {
            filters: {
                status: 'all',
            },
        };
    },
});
