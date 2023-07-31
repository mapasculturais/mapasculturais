app.component('opportunity-phase-support', {
    template: $TEMPLATES['opportunity-phase-support'],
    
    data() {
        let phases = $MAPAS.supportPhases;

        for (phase of phases) {
            if (phase.isLastPhase) {
                phases.splice(phases.indexOf(phase), 1);
            }
        }

        return {
            phases,
        }
    },
});
