app.component('evaluation-continuous-detail', {
    template: $TEMPLATES['evaluation-continuous-detail'],

    props: {
        registration: {
            type: Entity,
            required: true
        }
    },

    computed: {
        evaluationDetails() {
            return this.registration.evaluationsDetails ? this.registration.evaluationsDetails : $MAPAS.config.continuousEvaluationDetail.data?.evaluationsDetails;
        }
    },
});
