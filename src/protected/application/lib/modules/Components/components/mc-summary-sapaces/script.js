app.component('mc-summary-sapaces', {
    template: $TEMPLATES['mc-summary-sapaces'],

    setup() {
    },
    mounted() {
        return {}
    },
    props: {},

    data() {
        let entity = $MAPAS.requestedEntity;
        let avaliableEvaluationFields = $MAPAS.avaliableEvaluationFields
        let space = entity.spaceRelations ? entity.spaceRelations[0].space : null;
        let opportunity = $MAPAS.opportunity;

        return { entity, avaliableEvaluationFields, space, opportunity }
    },

    computed: {},

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
