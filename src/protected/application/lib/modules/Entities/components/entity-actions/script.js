app.component('entity-actions', {
    template: $TEMPLATES['entity-actions'],
    emits: [],

    setup() {
        const text = Utils.getTexts('entity-actions')
        return { text }
    },

    created() {},

    data() {
        return {}
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
        }
    },
    
    methods: {
        save() {
            this.entity.save();
            if (window.history.length > 2) {
                window.history.back();
            } else {
                window.location.href = $MAPAS.baseURL+'panel';
            }
        }
    },
});
