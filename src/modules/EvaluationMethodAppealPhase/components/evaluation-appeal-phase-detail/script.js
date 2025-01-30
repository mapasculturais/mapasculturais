app.component('evaluation-appeal-phase-detail', {
    template: $TEMPLATES['evaluation-appeal-phase-detail'],

    data() {
        return {
            evaluationEntities: {}
        };
    },

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
            if (!evaluation || !evaluation.entityEvaluation) {
                return null;
            }

            if (!this.evaluationEntities[evaluation.id]) {
                const entity = new Entity("registrationevaluation");
                entity.populate(evaluation.entityEvaluation);
                this.evaluationEntities[evaluation.id] = entity;
            }

            return this.evaluationEntities[evaluation.id];
        }
    }
});
