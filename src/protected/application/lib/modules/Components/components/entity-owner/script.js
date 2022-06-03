app.component('entity-owner', {
    template: $TEMPLATES['entity-owner'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    data() {
        return {
            owner: this.entity.owner || this.entity.parent || null
        }
    },
    
    props: {
        entity: {
            type: Entity,
            required: true
        },
        title: {
            type: String,
            default: 'Publicado por'
        },
        editable: {
            type: Boolean,
            default: false
        }
    }
    
});
