app.component('entity-actions', {
    template: $TEMPLATES['entity-actions'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    created() {},

    data() {
        return {}
    },

    props: {
        entity: {
            type: Entity,
            required: true 
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
