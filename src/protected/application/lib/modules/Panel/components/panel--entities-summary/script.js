app.component('panel--entities-summary', {
    template: $TEMPLATES['panel--entities-summary'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('panel--entities-summary')
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

    },

    data() {
        return {
        }
    },

    computed: {
    },
    
    methods: {
    },
});
