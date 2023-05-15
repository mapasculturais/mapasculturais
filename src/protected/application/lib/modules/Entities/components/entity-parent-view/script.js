app.component('entity-parent-view', {
    template: $TEMPLATES['entity-parent-view'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    data() {
        return {  }
    },

    computed: {
        parent() {
            console.log(this.entity)
            return this.entity.parent || null
        }
    },
    
    props: {
        label: {
            type: String,
            default: __('vinculado a ','entity-parent-edit'),
        },
        entity: {
            type: Entity,
            required: true
        },
    },
});
