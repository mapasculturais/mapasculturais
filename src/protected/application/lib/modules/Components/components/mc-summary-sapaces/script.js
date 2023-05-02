app.component('mc-summary-sapaces', {
    template: $TEMPLATES['mc-summary-sapaces'],

    props: {
        entity: {
            type: Entity,
            required: true
        }
    },

    data() {
        let avaliableEvaluationFields = $MAPAS.avaliableEvaluationFields
        let space = this.entity.spaceRelations[0]?.space ? this.entity.spaceRelations[0]?.space : null;
        let opportunity = this.entity.opportunity;

        return { avaliableEvaluationFields, space, opportunity }
    },

    methods: {
        canSee(item) {
            if (this.entity.currentUserPermissions['@control']) {
                return true
            }

            if (this.avaliableEvaluationFields[item]) {
                return true;
            }

            return false;
        },

    },
});
