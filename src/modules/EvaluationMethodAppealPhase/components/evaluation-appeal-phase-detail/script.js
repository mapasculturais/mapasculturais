app.component('evaluation-appealPhase-detail', {
    template: $TEMPLATES['evaluation-appealPhase-detail'],

    props: {
        registration: {
            type: Entity,
            required: true
        }
    },

    computed: {
        evaluationDetails() {
            return this.registration.evaluationsDetails ? this.registration.evaluationsDetails : $MAPAS.config.appealPhaseEvaluationDetail.data?.evaluationsDetails;
        }
    },
});
