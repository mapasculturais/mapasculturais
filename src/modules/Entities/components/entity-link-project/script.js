app.component('entity-link-project', {
    template: $TEMPLATES['entity-link-project'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    data() {
        return {  }
    },

    computed: {
        project() {
            return this.entity.project || null
        },
    },
    
    props: {
        entity: {
            type: Entity,
            required: true
        },
        title: {
            type: String,
            default: __('vinculado','entity-link-project'),
        },
        type: {
            type: String,
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
        label: {
            type: String,
            required: true
        }

    },

    methods: {
        changeProject(entity) {
            this.entity.project = entity;
            console.log(entity);
        },
    }
    
});
