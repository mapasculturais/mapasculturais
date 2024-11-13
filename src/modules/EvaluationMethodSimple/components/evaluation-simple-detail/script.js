app.component('evaluation-simple-detail', {
    template: $TEMPLATES['evaluation-simple-detail'],

    props: {
        registration: {
            type: Entity,
            required: true
        }
    },

    computed: {
        evaluationDetails() {
            return this.registration.evaluationsDetails ? this.registration.evaluationsDetails : $MAPAS.config.simpleEvaluationDetail.data?.evaluationsDetails;
        }
    },
});
