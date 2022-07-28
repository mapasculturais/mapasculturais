app.component('home-developers', {
    template: $TEMPLATES['home-developers'],
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
        },
        editable: {
            type: Boolean,
            default: false
        }
    },
    
    methods: {
        doSomething () {

        }
    },
});
