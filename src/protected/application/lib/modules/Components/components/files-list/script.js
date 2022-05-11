app.component('files-list', {
    template: $TEMPLATES['files-list'],
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
        files: {
            type: Object,
            required: true
        },
        title: {
            type: String,
            required: true
        }
    },
    
    methods: {
        doSomething () {

        }
    },
});
