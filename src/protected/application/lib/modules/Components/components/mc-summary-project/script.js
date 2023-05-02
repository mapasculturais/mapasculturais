app.component('mc-summary-project', {
    template: $TEMPLATES['mc-summary-project'],

    setup() {
        console.log('Teste')
    },
    props: {
        entity: {
            type: Entity,
            required: true
        }
    },

    data() {
        let avaliableEvaluationFields = $MAPAS.avaliableEvaluationFields
        let opportunity = this.entity.opportunity;
        let projectName = this.entity.projectName;

        return { avaliableEvaluationFields, projectName, opportunity }
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
