app.component('entity-actions', {
    template: $TEMPLATES['entity-actions'],
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
        save() {

        },
        publish() {

        },
        archive() {

        },
        delete() {

        }
    },
});
