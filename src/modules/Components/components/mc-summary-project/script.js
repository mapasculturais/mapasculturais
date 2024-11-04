app.component('mc-summary-project', {
    template: $TEMPLATES['mc-summary-project'],

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
        let opportunity = this.entity.opportunity;
        let projectName = this.entity.projectName;

        return { avaliableEvaluationFields, projectName, opportunity }
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
