app.component('entity-cover', {
    template: $TEMPLATES['entity-cover'],
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
        name: {
            type: String,
            default: ''
        }
    },
    
    methods: {
        doSomething () {

        }
    },
});
