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
            return this.entity.parent || null
        }
    },
    
    props: {
        entity: {
            type: Entity,
            required: true
        },
    },
});
