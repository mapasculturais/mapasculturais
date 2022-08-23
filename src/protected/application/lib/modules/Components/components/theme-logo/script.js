/**
 * Vue Lifecycle
 * 1. setup
 * 2. beforeCreate
 * 3. created
 * 4. beforeMount
 * 5. mounted
 * 
 * // sempre que há modificação nos dados
 *  - beforeUpdate
 *  - updated
 * 
 * 6. beforeUnmount
 * 7. unmounted                  
 */

app.component('theme-logo', {
    template: $TEMPLATES['theme-logo'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('theme-logo')
        return { text }
    },

    beforeCreate() { },
    created() { },

    beforeMount() { },
    mounted() { },

    beforeUpdate() { },
    updated() { },

    beforeUnmount() {},
    unmounted() {},

    props: {
        title: {
            type: String,
            default: 'mapas culturais'
        },
        subtitle: {
            type: String,
            default: ''
        },
        color: {
            type: String,
            default: $MAPAS.config.logo.color
        },
        href: {
            type: String,
            default: null
        }
    },

    /* data() {
        return { color1: $MAPAS.config.logo.color1 };
    }, */

    computed: {
    },
    
    methods: {
    },
});
