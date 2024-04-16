app.component('opportunity-results-table', {
    template: $TEMPLATES['opportunity-results-table'],

    data() {
        return {
            showColumns: $MAPAS.config.opportunityResultsTable.showColumns,
        };
    },

    computed: {
        lastPhase() {
            const lastPhase = $MAPAS.opportunityPhases[$MAPAS.opportunityPhases.length - 1];
            console.log(lastPhase);
            if (lastPhase.isLastPhase) {
                return lastPhase;
            } else {
                return undefined;
            }
        }
    },
});
