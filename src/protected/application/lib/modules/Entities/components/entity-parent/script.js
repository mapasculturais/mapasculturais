app.component('entity-parent', {
    template: $TEMPLATES['entity-parent'],
    emits: [],

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name]
        return { hasSlot }
    },

    data() {
        return {  }
    },

    computed: {
        owner() {
            return this.entity.owner || this.entity.parent || null
        }
    },
    
    props: {
        entity: {
            type: Entity,
            required: true
        },
        title: {
            type: String,
            default: 'vinculado por'
        },
        editable: {
            type: Boolean,
            default: false
        },
        type: {
            type: String,
            required: true
        },
    },

    methods: {
        changeOwner(entity) {
            if (this.entity.__objectType == 'entity.type') {
                this.entity.parent = entity;
            } else {
                this.entity.owner = entity;
            }
        }
    }
    
});
