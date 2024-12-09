app.component('opportunity-results-table', {
    template: $TEMPLATES['opportunity-results-table'],

    data() {
        return {
            visibleColumns: $MAPAS.config.opportunityResultsTable.visibleColumns,
            columns: $MAPAS.config.opportunityResultsTable.columns,
        };
    },

    computed: {
        lastPhase() {
            const lastPhase = $MAPAS.opportunityPhases[$MAPAS.opportunityPhases.length - 1];
            
            if (lastPhase.isLastPhase) {
                return lastPhase;
            } else {
                return undefined;
            }
        }
    },
});
