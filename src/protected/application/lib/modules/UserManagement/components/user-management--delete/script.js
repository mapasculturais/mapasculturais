app.component('user-management--delete', {
    template: $TEMPLATES['user-management--delete'],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    created() {

    },

    data() {
        return {
        }
    },

    props: {
        user: {
            type: Entity,
            required: true
        }
    },
    
    methods: {
        switchUser (user) {
            console.log('NÃO IMPLEMENTADO', user)
        }
    },
});
