app.component('entity-related-agents', {
    template: $TEMPLATES['entity-related-agents'],
    
    // define os eventos que este componente emite
    emits: ['namesDefined'],

    setup(props, { attrs, slots, emit, expose }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        editable: {
            type: Boolean,
            default: false
        }
    },
    
    methods: {
        hasGroups() {
            if (this.entity.relatedAgents == undefined) {
                return false;
            } else {
                delete this.entity.relatedAgents['group-admin'];
                return true;
            }
        }
    },
});
