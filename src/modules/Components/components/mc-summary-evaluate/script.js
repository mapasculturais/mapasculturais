app.component('mc-summary-evaluate', {
    template: $TEMPLATES['mc-summary-evaluate'],

    props: {
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },

    updated() {
        this.summary = this.global.summaryEvaluations;
    },

    data() {
        this.global.summaryEvaluations = $MAPAS.config.summaryEvaluations;

        return {
            summary: this.global.summaryEvaluations,
        }
    },

    computed: { 
        pending() {
            return this.summary.pending;
        },

        started() {
            return this.summary.started;
        },

        completed() {
            return this.summary.completed;
        },

        sent() {
            return this.summary.sent;
        },

        isActive() {
            return this.summary.isActive;
        }
    },
});
