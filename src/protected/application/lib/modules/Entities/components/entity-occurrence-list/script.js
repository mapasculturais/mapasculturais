app.component('entity-occurrence-list', {
    template: $TEMPLATES['entity-occurrence-list'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-occurrence-list')
        return { text }
    },

    props: {
        entity: {
            type: Entity,
            required: true,
        },
        editable: {
            type: Boolean,
            default: false
        },
    },

    data() {
        return {}
    },

    methods: {
        formatPrice(price) {
            if (/^\d+(?:\.\d+)?$/.test(price)) {
	            return parseFloat(price).toLocaleString('pt-BR', { style: 'currency', currency: __('currency', 'entity-occurrence-list')  });
            } else {
                return price;
            }
        },
    },
});
