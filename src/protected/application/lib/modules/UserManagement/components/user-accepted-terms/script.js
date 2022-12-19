app.component('user-accepted-terms', {
    template: $TEMPLATES['user-accepted-terms'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    created() {

    },

    data() {
        // Termo
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
    computed: {
        // politica de privacidade


    },

    methods: {

        formatDate(timestamp) {
            let date = new McDate(new Date(timestamp * 1000));
            return date.date('numeric') + ' - ' + date.time('numeric');
        }
    },
});
