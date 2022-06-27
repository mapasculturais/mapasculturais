app.component('entity-social-media', {
    template: $TEMPLATES['entity-social-media'],
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
