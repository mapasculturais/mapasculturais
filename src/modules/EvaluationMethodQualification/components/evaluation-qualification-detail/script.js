app.component('evaluation-qualification-detail', {
    template: $TEMPLATES['evaluation-qualification-detail'],

    props: {
        registration: {
            type: Entity,
            required: true
        }
    },

    computed: {
        evaluationDetails() {
            return this.registration.evaluationsDetails ? this.registration.evaluationsDetails : $MAPAS.config.qualificationEvaluationDetail.data?.evaluationsDetails;
        }
    },
});
