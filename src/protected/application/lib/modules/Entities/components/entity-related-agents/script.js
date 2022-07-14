app.component('entity-related-agents', {
    template: $TEMPLATES['entity-related-agents'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        editable: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            groups: {},
            newGroupName: ''
        }
    },
    
    methods: {
        hasGroups() {
            if (this.entity.relatedAgents == undefined) {
                return false;
            } else {
                for (var [groupName, group] of Object.entries(this.entity.relatedAgents)) {
                    if (groupName == "group-admin") {
                        continue;
                    } else {
                        this.groups[groupName] = group;
                    }
                }
                return true;
            }
        },

        addGroup(group) {
            if (!this.entity.relatedAgents[group]) {
                this.entity.relatedAgents[group] = [];
            }
            if (!this.entity.agentRelations[group]) {
                this.entity.agentRelations[group] = [];
            }
        },

        addAgent(group, agent) {
            this.entity.addRelatedAgent(group, agent);
        }
    },
});
