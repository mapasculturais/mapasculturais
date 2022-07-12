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
            groups: {}
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
        }
    },
});
