app.component('entity-data', {
    template: $TEMPLATES['entity-data'],

    props: {
        entity: {
            type: Entity,
            required: true
        },

        prop: {
            type: String,
            required: true,
        },

        label: {
            type: String,
        }
    },

    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-data')
        return { text, hasSlot }
    },

    computed: {
        description() {
            const description = this.entity.$PROPERTIES[this.prop];

            if(!description) {
                console.error(`Propriedade ${this.prop} não encontrada na entidade ${this.entity.__objectType}`);
            }
            return description;
        },

        propertyLabel() {
            return this.label || this.description.label;
        },

        propertyData() {
            return this.entity[this.prop];
        },

        propertyType() {
            return this.description.type;
        },
    },
});
