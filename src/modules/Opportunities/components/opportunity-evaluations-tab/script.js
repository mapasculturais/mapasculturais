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
            if($MAPAS.config.opportunityEvaluationsTab.isEvaluator){
                result = true;
            };

            return result;
        }
    },
    
    methods: { },
});
