app.component('home-search', {
    template: $TEMPLATES['home-search'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('home-search')
        return { text }
    },

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
