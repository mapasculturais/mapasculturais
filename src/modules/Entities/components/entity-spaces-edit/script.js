app.component('entity-spaces-edit', {
    template: $TEMPLATES['entity-spaces-edit'],

    setup() {
        const text = Utils.getTexts('entity-spaces-edit');
        const api = new API('notification', 'default');
        return { text, api };
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
        const config = $MAPAS.config?.entitySpacesEdit || {};
        return {
            pendingSent: config.pendingSent || [],
            pendingReceived: config.pendingReceived || [],
        };
    },

    computed: {
        parentSpace() {
            const parent = this.entity?.parent;
            if (parent == null || typeof parent !== 'object' || !parent.id) {
                return null;
            }
            return parent;
        },

        children() {
            const list = this.entity?.children || [];
            return Array.isArray(list) ? list : [];
        },

        linkedSpaces() {
            const items = [];

            if (this.parentSpace) {
                items.push({
                    key: `parent-${this.parentSpace.id}`,
                    role: 'parent',
                    space: this.parentSpace,
                });
            }

            this.children.forEach((child) => {
                const space = typeof child === 'object' ? child : { id: child };
                if (!space?.id) {
                    return;
                }
                if (this.parentSpace && String(space.id) === String(this.parentSpace.id)) {
                    return;
                }
                items.push({
                    key: `child-${space.id}`,
                    role: 'child',
                    space,
                });
            });

            return items;
        },

        linkedCount() {
            return this.linkedSpaces.length;
        },

        pendingCount() {
            return this.pendingSent.length + this.pendingReceived.length;
        },

        childIds() {
            return this.children.map((item) => item.id ?? item).filter(Boolean);
        },

        selectQuery() {
            const exclude = [this.entity.id];
            if (this.parentSpace?.id) {
                exclude.push(this.parentSpace.id);
            }
            this.childIds.forEach((id) => exclude.push(id));
            return {
                id: `!IN(${exclude.join(',')})`,
            };
        },
    },

    methods: {
        areas(space) {
            const terms = space?.terms?.area || [];
            return Array.isArray(terms) ? terms : [];
        },

        areasText(space) {
            return this.areas(space).map((term) => String(term).toUpperCase()).join(', ');
        },

        async addLink(selected) {
            // Vincula o espaço atual como integrante do espaço selecionado (supra espaço).
            this.entity.parent = selected;
            try {
                await this.entity.save();
            } catch (error) {
                // Se gerar workflow, a solicitação aparece em pendentes após reload.
                console.error(error);
            }
        },

        async removeParent() {
            this.entity.parent = null;
            await this.entity.save();
        },

        async removeChild(space) {
            const child = new Entity('space', space.id);
            child.populate(space);
            child.parent = null;
            await child.save();
            this.entity.children = (this.entity.children || []).filter((item) => {
                const id = item.id ?? item;
                return String(id) !== String(space.id);
            });
        },

        async removeLink(item) {
            if (item.role === 'parent') {
                await this.removeParent();
                return;
            }
            await this.removeChild(item.space);
        },

        async approveRequest(item) {
            if (!item.notificationId) {
                return;
            }
            const url = this.api.createUrl('approve', [item.notificationId]);
            await this.api.POST(url);
            this.pendingReceived = this.pendingReceived.filter((row) => row.requestId !== item.requestId);
            globalThis.location?.reload?.();
        },

        async rejectRequest(item) {
            if (!item.notificationId) {
                return;
            }
            const url = this.api.createUrl('reject', [item.notificationId]);
            await this.api.POST(url);
            this.pendingSent = this.pendingSent.filter((row) => row.requestId !== item.requestId);
            this.pendingReceived = this.pendingReceived.filter((row) => row.requestId !== item.requestId);
        },
    },
});
