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
        save() {
            const event = new Event("entitySave");
            this.entity.save().then(() => {
                window.dispatchEvent(event);
                //this.exit();
            });
        },
        exit() {
            window.location.href = this.entity.getUrl('single');
        },
    },
});
