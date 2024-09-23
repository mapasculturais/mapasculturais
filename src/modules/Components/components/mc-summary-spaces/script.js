app.component('mc-summary-spaces', {
    template: $TEMPLATES['mc-summary-spaces'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },

    },

    data() {
        let avaliableEvaluationFields = $MAPAS.avaliableEvaluationFields
        let space = this.entity.spaceRelations[0]?.space ? this.entity.spaceRelations[0]?.space : null;
        let opportunity = this.entity.opportunity;

        return { avaliableEvaluationFields, space, opportunity }
    },

    methods: {
        canSee(item) {
            let can = false;
            if(this.entity.currentUserPermissions['@control'] || this.entity.currentUserPermissions['view']){
                can = true
            }

            if (can &&!this.avaliableEvaluationFields[item]) {
                can = false;
            }
            
            return can;
        },

    },
});
