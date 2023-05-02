app.component('mc-summary-project', {
    template: $TEMPLATES['mc-summary-project'],

    setup() {
    },
    mounted() {
        return {}
    },
    props: {},

    data() {
        let entity = $MAPAS.requestedEntity;
        let avaliableEvaluationFields = $MAPAS.avaliableEvaluationFields
        let opportunity = $MAPAS.opportunity;
        let projectName = entity.projectName;

        return { entity, avaliableEvaluationFields, projectName, opportunity }
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
