app.component('opportunity-reports-static-charts', {
    template: $TEMPLATES['opportunity-reports-static-charts'],

    props: {
        opportunityId: {
            type: [String, Number],
            required: true,
        },
        filters: {
            type: Object,
            required: true, // { status, proponentTypes }
        },
    },

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente
        const text = Utils.getTexts('opportunity-reports-static-charts');
        return { text };
    },

    data() {
        return {
            charts: null,
            loading: false,
        };
    },

    computed: {
        chartCards() {
            if (!this.charts) {
                return [];
            }
            const labels = {
                registrationsByTime: this.text('chart_by_time'),
                registrationsByStatus: this.text('chart_by_status'),
                registrationsByEvaluation: this.text('chart_by_evaluation'),
                registrationsByEvaluationStatus: this.text('chart_by_evaluation_status'),
                registrationsByCategory: this.text('chart_by_category'),
            };
            const types = {
                registrationsByTime: 'line',
            };
            // mapeamento pros endpoints de export CSV existentes; "registrationsByEvaluation"
            // fica de fora porque a rota de export varia conforme o método de avaliação
            // (bar vs simples) e essa informação não chega até este componente hoje
            const exportActions = {
                registrationsByStatus: 'exportRegistrationsByStatus',
                registrationsByEvaluationStatus: 'exportRegistrationsByEvaluationStatus',
                registrationsByCategory: 'exportRegistrationsByCategory',
                registrationsByTime: 'registrationsByTime',
            };
            const api = new ReportsAPI();
            return Object.keys(this.charts).map(key => ({
                key,
                title: labels[key] || key,
                type: types[key] || 'pie',
                chart: this.charts[key],
                exportUrl: exportActions[key]
                    ? api.csvExportUrl(exportActions[key], this.opportunityId, this.filters, { action: key })
                    : null,
            }));
        },
    },

    watch: {
        filters: {
            deep: true,
            handler() {
                this.fetchCharts();
            },
        },
    },

    created() {
        this.fetchCharts();
    },

    methods: {
        async fetchCharts() {
            this.loading = true;
            const api = new ReportsAPI();
            this.charts = await api.getStaticCharts(this.opportunityId, this.filters);
            this.loading = false;
        },
    },
});
