app.component('user-accepted-terms', {
    template: $TEMPLATES['user-accepted-terms'],
    emits: [],

    data() {
        const terms = $MAPAS.config.LGPD;

        return {
            terms
        };
    },

    props: {
        user: {
            type: Entity,
            required: true
        },

    },
    methods: {

        formatDate(timestamp) {
            let date = new McDate(new Date(timestamp * 1000));
            return date.date('numeric') +'/'+ date.year('numeric') + ' - ' + date.time('numeric');
        }
    },
});
