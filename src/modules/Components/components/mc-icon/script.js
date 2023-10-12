app.component('mc-icon', {
    template: $TEMPLATES['mc-icon'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-icon')
        return { text }
    },

    props: {
        entity: {
            type: [Entity, Object],
            required: false
        },

        name: {
            type: String,
            required: false
        },

        isLink: {
            type: Boolean,
            default: false
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
            const iconset = $MAPAS.config.iconset;
            if (this.entity) {
                const e = this.entity;
                return iconset[`${e.__objectType}-${ e.type?.id || e.type}`] || iconset[e.__objectType] || iconset[e.__objectId];
            } else {

                return iconset[this.name];
            }
        },
    },
    
    methods: { },
});
