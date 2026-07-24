app.component('opportunity-reports-chart-card', {
    template: $TEMPLATES['opportunity-reports-chart-card'],

    props: {
        title: {
            type: String,
            required: true,
        },
        type: {
            type: String,
            required: true, // pie | bar | horizontalBar | line | table
        },
        chart: {
            type: Object,
            required: true, // { labels, data|datasets, backgroundColor }
        },
        exportUrl: {
            type: String,
            default: null,
        },
        editable: {
            type: Boolean,
            default: false,
        },
        deletable: {
            type: Boolean,
            default: false,
        },
    },

    emits: ['edit', 'delete'],
});
