app.component('mc-chart', {
    template: $TEMPLATES['mc-chart'],

    props: {
        // pie | bar | horizontalBar | line | table
        type: {
            type: String,
            required: true,
        },
        labels: {
            type: Array,
            default: () => [],
        },
        // uso avançado: múltiplos datasets já formatados para o Chart.js
        datasets: {
            type: Array,
            default: null,
        },
        // atalho para gráficos de um único dataset (a maioria dos gráficos estáticos)
        data: {
            type: Array,
            default: null,
        },
        backgroundColor: {
            type: Array,
            default: null,
        },
    },

    computed: {
        chartComponent() {
            if (this.type === 'pie') {
                return 'ChartPie';
            }
            if (this.type === 'line') {
                return 'ChartLine';
            }
            // bar e horizontalBar usam o mesmo componente, diferenciados por options.indexAxis
            return 'ChartBar';
        },

        chartData() {
            if (this.datasets) {
                return { labels: this.labels, datasets: this.datasets };
            }
            return {
                labels: this.labels,
                datasets: [{ data: this.data || [], backgroundColor: this.backgroundColor }],
            };
        },

        chartOptions() {
            const options = { responsive: true, maintainAspectRatio: false };
            if (this.type === 'horizontalBar') {
                options.indexAxis = 'y';
            }
            return options;
        },
    },
});
