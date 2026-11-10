app.component('entity-actions', {
    template: $TEMPLATES['entity-actions'],
    emits: [],

    setup() {
        const text = Utils.getTexts('entity-actions')
        return { text }
    },

    computed: {
        empty() {
            return !(this.hasMoreActions || this.hasPrimaryActions);
        },

        entityType() {
            switch (this.entity['__objectType']) {
                case 'agent':
                    return __('Agente', 'entity-actions');

                case 'event':
                    return __('Evento', 'entity-actions');

                case 'opportunity':
                    return __('Oportunidade', 'entity-actions');

                case 'space':
                    return __('Espaço', 'entity-actions');
                
                case 'project':
                    return __('Projeto', 'entity-actions');
            }
        },

        usePrivate() {
            const description = $DESCRIPTIONS[this.entity.__objectType];
            return !!description.status.options.private;
        },

        permissions() {
            return this.entity.currentUserPermissions || {};
        },

        hasExtraActions() {
            const isOpportunity = this.entity.__objectType === 'opportunity';
            const notArchived = this.entity.status != -2;
            const canModify = !!this.permissions.modify;

            // Exportar vale para qualquer oportunidade; duplicar/modelo também entram nesse bloco
            return !!(isOpportunity && canModify && notArchived);
        },

        hasAdminActions() {
            const canPrivate = this.usePrivate
                && this.entity.status == 1
                && !!this.permissions.makePrivate;
            const canArchive = !!this.permissions.archive && this.entity.status != -2;
            const canDelete = !!this.permissions.remove && this.canDelete;

            return !!(canPrivate || canArchive || canDelete);
        },

        hasMoreActions() {
            return this.hasExtraActions || this.hasAdminActions;
        },

        hasPrimaryActions() {
            if (this.editable) {
                return !!(this.permissions.modify || this.permissions.publish);
            }

            return !!this.permissions.modify;
        },
    },

    props: {
        entity: {
            type: Entity,
            required: true 
        },
        editable: {
            type: Boolean,
            default: false
        },
        canDelete: {
            type: Boolean,
            default: true
        }
    },
    
    methods: {
        onMoreActionsClick(event, close) {
            if (event.target.closest('button, a')) {
                close?.();
            }
        },

        async makePrivate(modal) {
            const updateMethod = $MAPAS.config['entity-actions']['updateMethod'];

            try {
                await this.entity.save({updateMethod});
                await this.entity.validate();
                await this.entity.makePrivate();
                if(modal) {
                    modal.close();
                }
            } catch (error) {
                console.error(error);
            }
        },

        async publish(modal) {
            const updateMethod = $MAPAS.config['entity-actions']['updateMethod'];

            try {
                await this.entity.save({updateMethod});
                await this.entity.validate();
                await this.entity.publish();
                if(modal) {
                    modal.close();
                }
            } catch (error) {
                console.error(error);
            }
        },
        save() {
            const event = new Event("entitySave");

            const updateMethod = $MAPAS.config['entity-actions']['updateMethod'];

            this.entity.save({updateMethod}).then(() => {
                this.entity.validate();
                window.dispatchEvent(event);
            });
        },
        exit() {
            window.location.href = this.entity.getUrl('single');
        },
    },
});
