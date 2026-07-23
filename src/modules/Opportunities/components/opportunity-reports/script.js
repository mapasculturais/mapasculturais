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
                proponentTypes: [],
                ranges: [],
            },
        };
    },

    computed: {
        proponentTypeOptions() {
            return this.entity.registrationProponentTypes || [];
        },

        rangeOptions() {
            return (this.entity.registrationRanges || []).map(range => range.label);
        },
    },
});
