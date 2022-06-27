app.component('create-space', {
    template: $TEMPLATES['create-space'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    created() {

    },

    data() {
        return {
            message: 'test'
        }
    },

    props: {
        entity: {
            type: Entity,
            required: true 
        }
    },
    
    methods: {
        doSomething () {

        }
    },
});
