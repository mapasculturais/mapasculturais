app.component('entity-list', {
    template: $TEMPLATES['entity-list'],
    emits: [],
    
    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-list')
        return { text }
    },

    data() {
        const ids = (this.propertyName=="opportunities") ? this.entity.ownedOpportunities.concat(this.entity.relatedOpportunities) : this.entity[this.propertyName];
        
        return {
            query: {
                'id': `IN(${ids})`,
            },
        };
    },

    props: {

        entity: {
            type: Entity,
            required: true
        },

        title: {
            type: String,
            required: true,
        },

        editable: {
            type: Boolean,
            default: false,
            
        },
        propertyName: {
            type: String,
            required: true,
        },
        type: {
            type: String,
            required: true,
        },

    },

    methods: {

    },
    
});
