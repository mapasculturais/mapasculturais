app.component('entity-owner', {
    template: $TEMPLATES['entity-owner'],
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
            default: 'Publicado por'
        },
        editable: {
            type: Boolean,
            default: false
        }
    },

    methods: {
        changeOwner(entity) {
            if (this.entity.__objectType == 'agent') {
                this.entity.parent = entity;
            } else {
                this.entity.owner = entity;
            }
        }
    }
    
});
