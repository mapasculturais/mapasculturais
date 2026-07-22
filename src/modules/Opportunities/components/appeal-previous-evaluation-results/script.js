app.component('appeal-previous-evaluation-results', {
    template: $TEMPLATES['appeal-previous-evaluation-results'],

    data() {
        const config = $MAPAS.config.appealPreviousEvaluationResults;

        if (!config?.registration || !config?.phase) {
            return {
                registration: null,
                phase: null,
            };
        }

        const registration = new Entity('registration');
        registration.populate(config.registration);

        const phase = new Entity('evaluationmethodconfiguration');
        phase.populate(config.phase);

        return {
            registration,
            phase,
        };
    },

    computed: {
        hasPreviousEvaluation() {
            return this.registration && this.phase;
        },
    },
});
