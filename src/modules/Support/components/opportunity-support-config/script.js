app.component('opportunity-support-config', {
    template: $TEMPLATES['opportunity-support-config'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },

    data() {
        return {}
    },

    computed: {
        relations(){
           return this.entity.agentRelations["@support"];
        }
    },
    
    methods: {
        addAgent(agent) {
            this.entity.addRelatedAgent('@support',agent);
        },
        removeAgent(agent) {
            this.entity.removeAgentRelation('@support',agent);
        }
    },
});
