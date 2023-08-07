app.component('mc-summary-agent', {
    template: $TEMPLATES['mc-summary-agent'],

    props: {
        entity:{
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
        let owner = this.entity.agentsData.owner;

        return { avaliableEvaluationFields, owner, opportunity }
    },
    methods: {
        canSee(item) {
            if(this.entity.currentUserPermissions['@control'] || this.entity.currentUserPermissions['view']){
                return true
            }

            if (this.avaliableEvaluationFields[item]) {
                return true;
            }
            
            return false;
        },
    },
});
