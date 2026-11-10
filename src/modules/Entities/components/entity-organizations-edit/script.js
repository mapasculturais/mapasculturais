app.component('entity-organizations-edit', {
    template: $TEMPLATES['entity-organizations-edit'],

    setup() {
        const text = Utils.getTexts('entity-organizations-edit');
        return { text };
    },

    props: {
        entity: {
            type: Entity,
            required: true,
        },
        editable: {
            type: Boolean,
            default: true,
        },
    },

    data() {
        const orgs = $MAPAS.agentOrganizations || { collab: [], pending: [], transferred: [] };
        return {
            ownerCount: 0,
            adminCount: 0,
            collabRelations: orgs.collab || [],
            pendingRelations: orgs.pending || [],
            transferredOrgs: orgs.transferred || [],
        };
    },

    computed: {
        collabCount() {
            return this.collabRelations.length;
        },

        pendingCount() {
            return this.pendingRelations.length;
        },

        transferredCount() {
            return this.transferredOrgs.length;
        },

        collabIds() {
            return this.collabRelations.map((item) => item.orgId);
        },

        pendingIds() {
            return this.pendingRelations.map((item) => item.orgId);
        },

        transferredIds() {
            return this.transferredOrgs.map((item) => item.orgId);
        },

        ownerQuery() {
            return {
                parent: `EQ(${this.entity.id})`,
                '@order': 'name ASC',
                status: 'GTE(0)',
            };
        },

        adminQuery() {
            return {
                '@permissions': '@control',
                '@order': 'name ASC',
                status: 'GTE(0)',
                type: 'EQ(2)',
                user: '!EQ(@me)',
            };
        },

        selectFields() {
            return 'id,name,type,terms,files.avatar,singleUrl,createTimestamp,parent.{id,name,singleUrl}';
        },
    },

    methods: {
        areas(agent) {
            const terms = agent?.terms?.area || [];
            return Array.isArray(terms) ? terms : [];
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
            } else if (typeof value?.date === 'function') {
                return value.date('2-digit year');
            }

            return mcDate ? mcDate.date('2-digit year') : '';
        },

        collabRelationFor(orgId) {
            return this.collabRelations.find((item) => Number(item.orgId) === Number(orgId));
        },

        pendingRelationFor(orgId) {
            return this.pendingRelations.find((item) => Number(item.orgId) === Number(orgId));
        },

        transferredFor(orgId) {
            return this.transferredOrgs.find((item) => Number(item.orgId) === Number(orgId));
        },

        pendingRoleLabel(orgId) {
            const relation = this.pendingRelationFor(orgId);
            if (!relation) {
                return this.text('roleCollaborator');
            }
            return relation.hasControl ? this.text('roleAdmin') : this.text('roleCollaborator');
        },

        onOwnerLoaded(entities) {
            this.ownerCount = entities?.length || 0;
        },

        onAdminLoaded(entities) {
            this.adminCount = entities?.length || 0;
        },

        onCreateOrg(org) {
            this.ownerCount += 1;
            if (!this.entity.children) {
                this.entity.children = [];
            }
            const exists = this.entity.children.some((item) => {
                const id = item?.id ?? item;
                return Number(id) === Number(org.id);
            });
            if (!exists) {
                this.entity.children.push(org);
            }
        },

        async leaveOrganization(org) {
            const relation = this.collabRelationFor(org.id);
            if (!relation?.group) {
                return;
            }

            const owner = new Entity('agent', org.id);
            await owner.removeAgentRelation(relation.group, this.entity);

            this.collabRelations = this.collabRelations.filter(
                (item) => Number(item.orgId) !== Number(org.id)
            );

            const messages = useMessages();
            messages.success(this.text('leaveSuccess'));
        },
    },
});
