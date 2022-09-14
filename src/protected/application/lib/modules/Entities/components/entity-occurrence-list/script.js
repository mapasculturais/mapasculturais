app.component('entity-occurrence-list', {
    template: $TEMPLATES['entity-occurrence-list'],
    
    // define os eventos que este componente emite
    emits: ['namesDefined'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('entity-occurrence-list')
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
        return {};
    },

    computed: {
    },
    
    methods: {
    },
});
