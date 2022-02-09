app.component('card-user-management', {
    template: $TEMPLATES['card-user-management'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    created() {

    },

    data() {
        return {
            message: ''
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
