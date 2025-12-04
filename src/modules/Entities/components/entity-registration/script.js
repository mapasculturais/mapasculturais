app.component('entity-registration', {
    template: $TEMPLATES['entity-registration'],

    props: {
        entity: {
            type: Entity,
            required: true
        },
        editable: {
            type: Boolean,
            default: false
        },
        classes: {
            type: [String, Array, Object],
            required: false
        },
    },

    data() {
        return {
        }
    },

    
    methods: {
      
    },
});
