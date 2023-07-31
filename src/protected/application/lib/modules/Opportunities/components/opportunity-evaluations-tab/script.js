app.component('opportunity-evaluations-tab', {
    template: $TEMPLATES['opportunity-evaluations-tab'],
    props: {
        entity: {
            type: Entity,
            required: true
        },
    },

    data() {
        phases = $MAPAS.opportunityPhases;
        return {
            phases
         }
    },

    computed: { 
        isEvaluator(){
            if(this.entity.currentUserPermissions['@control']){
                return true;
            }
            
            let result = false;
            this.phases.forEach(phase => {
                if(phase.currentUserPermissions.evaluateOnTime){
                    result = true;
                    return;
                }
            });

            return result;
        }
    },
    
    methods: { },
});
