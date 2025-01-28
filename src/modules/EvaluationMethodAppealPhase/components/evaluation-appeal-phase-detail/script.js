app.component('evaluation-appeal-phase-detail', {
    template: $TEMPLATES['evaluation-appeal-phase-detail'],

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

    methods: {
        getEvaluationEntity(evaluation) {
            const entity = new Entity("registrationevaluation");
            entity.populate(evaluation.entityEvaluation);
            return entity;
        }
    }
});
