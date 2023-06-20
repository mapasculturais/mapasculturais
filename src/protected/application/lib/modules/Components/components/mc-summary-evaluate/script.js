app.component('mc-summary-evaluate', {
    template: $TEMPLATES['mc-summary-evaluate'],

    setup() { },

    props: {
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },

    data() {
        return {
            summary: $MAPAS.config.summaryEvaluate,
        }
    },

    computed: {},

    methods: {},
});
