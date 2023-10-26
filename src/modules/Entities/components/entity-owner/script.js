app.component('entity-owner', {
    template: $TEMPLATES['entity-owner'],
    emits: [],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-owner')
        return { text }
    },
    
    props: {
        entity: {
            type: Entity,
            required: true
        },
        title: {
            type: String,
            default: __('publicado por', 'entity-owner')
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
            query: {}
        }
    },

    mounted() {
        if (this.entity.__objectType === 'agent') {
            this.query.id = `!EQ(${this.entity.id})`;
        } else {
            this.query.id = `!IN(${this.owner?.id})`;
        }
    },

    computed: {
        owner() {
            return this.entity.owner || this.entity.parent;
        }
    },

    methods: {
        changeOwner(entity) {
            if (this.entity.__objectType == 'agent') {
                this.entity.parent = entity;
            } else {
                this.entity.owner = entity;
            }
            this.query.id = `!IN(${this.owner?.id})`;
        }
    }
    
});
