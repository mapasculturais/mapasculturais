app.component('entity-actions', {
    template: $TEMPLATES['entity-actions'],
    emits: [],

    setup() {
        const text = Utils.getTexts('entity-actions')
        return { text }
    },

    created() {},

    mounted() {
        const buttons1 = this.$refs.buttons1?.childElementCount;
        const buttons2 = this.$refs.buttons2?.childElementCount;
        this.empty = !(buttons1 || buttons2);
    },

    data() {
        const empty = false;
        return {empty}
    },

    computed: {
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
        }
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
