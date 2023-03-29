app.component('registration-related-agents', {
    template: $TEMPLATES['registration-related-agents'],

    props: {
        registration: {
            type: Entity,
            required: true
        },
    },

    data() {
        return { 
            opportunity: this.registration.opportunity,
        }
    },

    computed: {
        agentRelations() {
            const relations = [];

            for (let relation of $MAPAS.config.registrationRelatedAgents) {
                const groupName = relation.agentRelationGroupName;
                const metadata = 'useAgentRelation' + groupName[0].toUpperCase() + groupName.slice(1);
                
                if(!this.opportunity[metadata]) {
                    continue;
                }
                
                if (this.opportunity[metadata] != 'dontUse') {
                    if (this.opportunity[metadata] == 'required') {
                        relation.required = true;
                    }
                    relations.push(relation);
                }
            }

            return relations;
        }
    },
    
    methods: {
        selectAgent(agent, relation) {
            let agentRelations = this.registration.agentRelations[relation.agentRelationGroupName];
            let relatedAgents = this.registration.relatedAgents[relation.agentRelationGroupName];

            if (agentRelations) {
                if (Object.keys(agentRelations).length > 0) {
                    agentRelations.pop();
                }
            }

            if (relatedAgents) {
                if (Object.keys(relatedAgents).length > 0) {
                    relatedAgents.pop();
                }
            }
            
            this.registration.addRelatedAgent(relation.agentRelationGroupName, agent);
        },
        removeAgent(agent, relation) {
            this.registration.removeAgentRelation(relation.agentRelationGroupName, agent);
        },
        hasRelations(relation) {
            if (relation) {
                if (Object.keys(relation).length > 0) {
                    return true;
                }
            }
            return false;
        }
    },
});