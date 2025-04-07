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
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
        title: {
            type: String,
            default: __('Administrado por', 'entity-admins'),
        },
    },

    computed: {
        isEditable() {
            return this.entity.currentUserPermissions.createAgentRelationWithControl && this.editable;
        },
        query() {
            const ids = this.group.map((item) => item.agent.id).join(',');

            let idFilter = '';
            let query = {}

            if (this.entity.__objectType === 'agent') {
                idFilter = ids ? `!IN(${ids}, ${this.entity.id})` : `!EQ(${this.entity.id})`;
            } else {
                idFilter = ids ? `!IN(${ids})` : '';
            }

            query['type'] = 'EQ(1)';

            if (idFilter) {
                query['id'] = idFilter;
            }
            
            query['parent'] = 'NULL()'

            return query;

        },
        group() {
            return this.entity.agentRelations?.['group-admin'] || [];
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