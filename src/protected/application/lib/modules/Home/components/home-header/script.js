app.component('home-header', {
    template: $TEMPLATES['home-header'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('home-header')
        return { text }
    },

    props: {
        title: {
            type: String,
            default: __('title', 'home-header')
        },
        description: {
            type: String,
            default: __('description', 'home-header')
        }
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
