app.component('user-mail', {
    template: $TEMPLATES['user-mail'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    created() {

    },

    data() {
      
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
