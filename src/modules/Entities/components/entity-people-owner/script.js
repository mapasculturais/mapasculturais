app.component('entity-people-owner', {
    template: $TEMPLATES['entity-people-owner'],

    setup() {
        const text = Utils.getTexts('entity-people-owner');
        return { text };
    },

    props: {
        entity: {
            type: Entity,
            required: true,
        },
        classes: {
            type: [String, Array, Object],
            required: false,
        },
        emptyMessage: {
            type: String,
            default: __('empty', 'entity-people-owner'),
        },
    },

    data() {
        const config = $MAPAS.config?.entityPeopleOwner || {};
        let requestData = null;

        if (config.requestData?.id) {
            requestData = new Entity('RequestChangeOwnership', config.requestData.id);
            requestData.populate(config.requestData);
        }

        return {
            query: {},
            destinationName: config.destinationName || null,
            hasRequest: !!config.hasRequest,
            requestData,
        };
    },

    mounted() {
        this.updateQuery();
    },

    computed: {
        owner() {
            const parent = this.entity?.parent;
            if (parent == null || typeof parent !== 'object') {
                return null;
            }
            return parent;
        },

        ownerTags() {
            if (!this.owner?.id) {
                return [];
            }

            const tags = [this.text('ownerRole')];
            const relations = this.entity?.agentRelations || {};

            for (const [groupName, group] of Object.entries(relations)) {
                if (groupName === 'group-admin' || groupName === '@support') {
                    continue;
                }

                const belongs = (group || []).some((relation) => {
                    const id = relation?.agent?.id;
                    return id != null && String(id) === String(this.owner.id);
                });

                if (belongs && !tags.includes(groupName)) {
                    tags.push(groupName);
                }
            }

            return tags;
        },
    },

    methods: {
        updateQuery() {
            if (this.entity.__objectType === 'agent') {
                const excludeIds = [this.entity.id];
                if (this.owner?.id) {
                    excludeIds.push(this.owner.id);
                }
                this.query = { id: `!IN(${excludeIds.join(',')})` };
                return;
            }

            this.query = this.owner?.id ? { id: `!IN(${this.owner.id})` } : {};
        },

        areas(agent) {
            const terms = agent?.terms?.area || [];
            return Array.isArray(terms) ? terms : [];
        },

        areasText(agent) {
            return this.areas(agent).map((term) => String(term).toUpperCase()).join(', ');
        },

        formatDate(value) {
            if (!value) {
                return '';
            }

            if (typeof value === 'string' && /^\d{2}\/\d{2}\/\d{4}$/.test(value)) {
                return value;
            }

            let mcDate = null;
            if (value instanceof McDate) {
                mcDate = value;
            } else if (typeof value === 'string') {
                mcDate = new McDate(value);
            } else if (typeof value?.date === 'string' && value.date) {
                mcDate = new McDate(value.date);
            }

            return mcDate ? mcDate.date('2-digit year') : '';
        },

        changeOwner(agent) {
            if (this.entity.__objectType === 'agent') {
                this.entity.parent = agent;
            } else {
                this.entity.owner = agent;
            }

            this.entity.save();

            this.hasRequest = true;
            this.destinationName = agent.name;
            this.updateQuery();
        },
    },
});
