app.component('entity-people-collaborators', {
    template: $TEMPLATES['entity-people-collaborators'],

    setup() {
        const text = Utils.getTexts('entity-people-collaborators');
        return { text };
    },

    props: {
        entity: {
            type: Entity,
            required: true,
        },
        emptyMessage: {
            type: String,
            default: __('empty', 'entity-people-collaborators'),
        },
        classes: {
            type: [String, Array, Object],
            required: false,
        },
        preview: {
            type: Boolean,
            default: false,
        },
        previewLimit: {
            type: Number,
            default: 3,
        },
    },

    data() {
        return {
            viewMode: 'group', // group | people
        };
    },

    computed: {
        groups() {
            const relations = this.entity?.agentRelations || {};
            const result = [];

            for (const [groupName, group] of Object.entries(relations)) {
                if (groupName === 'group-admin' || groupName === '@support') {
                    continue;
                }

                const agents = (group || [])
                    .map((relation) => relation?.agent)
                    .filter((agent) => agent?.id);

                if (!agents.length) {
                    continue;
                }

                result.push({
                    name: groupName,
                    agents,
                });
            }

            return result;
        },

        flatAgents() {
            const byId = new Map();

            for (const group of this.groups) {
                for (const agent of group.agents) {
                    if (!byId.has(agent.id)) {
                        byId.set(agent.id, agent);
                    }
                }
            }

            return Array.from(byId.values());
        },

        sortedFlatAgents() {
            return [...this.flatAgents].sort((a, b) =>
                String(a?.name || '').localeCompare(String(b?.name || ''), 'pt', { sensitivity: 'base' })
            );
        },

        previewAgents() {
            return this.flatAgents.slice(0, this.previewLimit);
        },

        hasItems() {
            return this.flatAgents.length > 0;
        },

        totalCount() {
            return this.flatAgents.length;
        },

        ownerId() {
            const parent = this.entity?.parent;
            if (parent == null) {
                return null;
            }
            return typeof parent === 'object' ? parent.id : parent;
        },

        adminIds() {
            const admins = this.entity?.agentRelations?.['group-admin'] || [];
            return new Set(
                admins
                    .map((relation) => relation?.agent?.id)
                    .filter((id) => id !== null && id !== undefined && id !== '')
            );
        },

        tagsByAgentId() {
            const map = {};

            for (const group of this.groups) {
                for (const agent of group.agents) {
                    if (!map[agent.id]) {
                        map[agent.id] = [];
                    }
                    if (!map[agent.id].includes(group.name)) {
                        map[agent.id].push(group.name);
                    }
                }
            }

            for (const agentId of Object.keys(map)) {
                const id = Number.isNaN(Number(agentId)) ? agentId : Number(agentId);
                const tags = map[agentId];

                if (this.adminIds.has(id) || this.adminIds.has(agentId)) {
                    const label = this.text('adminRole');
                    if (!tags.includes(label)) {
                        tags.push(label);
                    }
                }

                if (this.ownerId != null && (String(this.ownerId) === String(agentId) || this.ownerId === id)) {
                    const label = this.text('ownerRole');
                    if (!tags.includes(label)) {
                        tags.push(label);
                    }
                }
            }

            return map;
        },
    },

    methods: {
        setViewMode(mode) {
            this.viewMode = mode;
        },

        agentTags(agentOrId) {
            const id = agentOrId?.id ?? agentOrId;
            if (id == null) {
                return [];
            }
            return this.tagsByAgentId[id] || this.tagsByAgentId[String(id)] || [];
        },

        areas(entity) {
            const terms = entity?.terms?.area || [];
            return Array.isArray(terms) ? terms : [];
        },

        areasText(entity) {
            return this.areas(entity).map((term) => String(term).toUpperCase()).join(', ');
        },
    },
});
