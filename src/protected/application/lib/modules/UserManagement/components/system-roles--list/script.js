app.component('system-roles--list', {
    template: $TEMPLATES['system-roles--list'],

    setup () {
        let messages = useMessages();
    },
    
    props: {
        name: {
            type: String,
            default: 'default'
        },
        query: {
            type: Object,
            default: {}
        }
    },

    methods: {
    },
});
