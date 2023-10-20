app.component('opportunity-list', {
    template: $TEMPLATES['opportunity-list'],

    data() {
        console.log(this.ids)
        return {
            query: {
                'id': `IN(${this.ids})`,
            },
            type: 'opportunity',
        };
    },

    async created() {

    },

    props: {
        ids: {
            type: [Array, Object],
            required: true,
        }
    },
    methods: {
    },


    computed: {

    }
});