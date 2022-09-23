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
        let username = this.entity.profile?.name || '';
        return {
            username
        };
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
