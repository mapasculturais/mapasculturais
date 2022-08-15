app.component('search', {
    template: $TEMPLATES['search'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('search')
        return { text }
    },

    props: {
    },

    data() {
        return {
            query: {},
        }
    },

    computed: {
    },
    
    methods: {
    },
});
