app.component('oc-entities', {
    template: $TEMPLATES['oc-entities'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        tabGroups: {
            type: [Boolean, Object],
            default: false
        }
    },
    computed: {
      
    },
    data() {
        return {}
    },
    methods: {}
});
