app.component('panel--card-user', {
    template: $TEMPLATES['panel--card-user'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    created() {

    },

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
        
    },
});
