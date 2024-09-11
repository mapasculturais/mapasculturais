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
            query: {},
            destinationName: null
        }
    },

    mounted() {
        if (this.entity.__objectType === 'agent') {
            this.query.id = `!EQ(${this.entity.id})`;
        } else {
            this.query.id = `!IN(${this.owner?.id})`;
        }

        const api = new API(this.requestData.destinationEntity);
        api.findOne(this.requestData.destinationId).then(data => {
            this.destinationName = data.name;
        });;
    },

    computed: {
        owner() {
            return this.entity.owner || this.entity.parent;
        },
        hasRequest() {
            return $MAPAS.config['entityOwner'].hasRequest
        },
        requestData() {
            return $MAPAS.config['entityOwner'].requestData
        },
    },

    methods: {
        
        changeOwner(entity) {
            if (this.entity.__objectType == 'agent') {
                this.entity.parent = entity;
            } else {
                this.entity.owner = entity;
            }

            this.entity.save();

            this.query.id = `!IN(${this.owner?.id})`;

            setTimeout(() => {
                window.location.reload(true);
            }, 1500);
        }
    }
    
});
