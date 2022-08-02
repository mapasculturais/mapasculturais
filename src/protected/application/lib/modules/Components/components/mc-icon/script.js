app.component('mc-icon', {
    template: $TEMPLATES['mc-icon'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-icon')
        return { text }
    },

    props: {
        entity: {
            type: Entity,
            required: false
        },

        of: {
            type: String,
            required: false
        },

        /**
         * @todo implementar outras propriedades do componente iconify 
         * https://docs.iconify.design/icon-components/vue/#properties
         */
    },

    data() {
        return {}
    },

    computed: {
        icon() {
            // @todo colocar o iconset de forma configurável
            const iconset = $MAPAS.config.iconset;
            if (this.entity) {
                const e = this.entity;

                return iconset[`${e.__objectType}-${e.type.id}`] || iconset[e.__objectType];
            } else {

                return iconset[this.of];
            }
        },
    },
    
    methods: { },
});
