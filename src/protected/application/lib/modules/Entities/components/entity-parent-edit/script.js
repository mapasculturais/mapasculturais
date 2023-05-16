app.component('entity-parent-edit', {
    template: $TEMPLATES['entity-parent-edit'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    data() {
        const query = {
            id: `!EQ(${this.entity.id})`
        }
        return { query }
    },

    computed: {
        parent() {
            return this.entity.parent || null
        }
    },
    
    props: {
        entity: {
            type: Entity,
            required: true
        },
        title: {
            type: String,
            default: __('vinculado','entity-parent-edit'),
        },
        type: {
            type: String,
            required: true
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
        changeParent(entity) {
            this.entity.parent = entity;
        }
    }
    
});
