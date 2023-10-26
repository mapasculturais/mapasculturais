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
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },

    data() {
        return {
            newGroupName: ''
        }
    },

    computed: {
        queries() {
            const result = {};
            for (var [groupName, group] of Object.entries(this.entity.agentRelations)) {
                const ids = group.map((item) => item.agent.id).join(',');

                if (this.entity.__objectType === 'agent') {
                    result[groupName] = ids ? {id: `!IN(${ids}, ${this.entity.id})`} : {id: `!EQ(${this.entity.id})`};
                } else {
                    result[groupName] = ids ? {id: `!IN(${ids})`} : {};
                }
            }
            return result;
        },

        groups() {
            let groups = {};
            for (var [groupName, group] of Object.entries(this.entity.agentRelations)) {
                if (groupName == "group-admin" || groupName == '@support') {
                    continue;
                } else {
                    group.newGroupName = groupName;
                    groups[groupName] = group;
                }
            }

            return groups;
        }
    },
    
    methods: {
        hasGroups() {
            if (Object.keys(this.groups).length > 0) {
                return true;
            } else {
                return false;
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
        },

        removeAgent(group, agent) {
            this.entity.removeAgentRelation(group, agent);
        },

        removeGroup(group) {
            this.entity.removeAgentRelationGroup(group);
        },

        renameGroup(oldName, newName, popover) {
            this.entity.renameAgentRelationGroup(oldName, newName).then(() => popover.close());
        },
    },
});
