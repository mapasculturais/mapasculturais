app.component('__template__', {
    template: $TEMPLATES['__template__'],
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
