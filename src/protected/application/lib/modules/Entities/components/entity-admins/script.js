app.component('entity-admins', {
    template: $TEMPLATES['entity-admins'],

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
            group: {}
        }
    },

    methods: {
        hasGroups() {
            if (this.entity.relatedAgents == undefined) {
                return false;
            } else {
                for (var [groupName, group] of Object.entries(this.entity.relatedAgents)) {
                    if (groupName == "group-admin") {
                        this.group[groupName] = group;
                    }
                }
                return true;
            }
        }
    },
});