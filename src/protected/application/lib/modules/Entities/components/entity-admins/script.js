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

    computed: {
        query() {
            const ids = this.group.map((item) => item.id).join(',');
            return ids ? {id: `!IN(${ids})`} : {};

        },
        group() {
            return  this.entity.relatedAgents?.['group-admin'] || [];
        }
    },

    methods: {
        addAgent(agent) {
            this.entity.addAdmin(agent);
        },

        removeAgent(agent) {
            this.entity.removeAgentRelation('group-admin', agent);
        },
    },
});