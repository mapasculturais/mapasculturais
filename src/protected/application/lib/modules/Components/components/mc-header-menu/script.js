app.component('mc-header-menu', {
    template: $TEMPLATES['mc-header-menu'],

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('mc-header-menu')
        return { text }
    },

    props: {
    },

    data() {
        return {
            openMobile: false,
        }
    },

    computed: {
    },
    
    methods: {
        toggleMobile() {
            this.openMobile = !this.openMobile;
        }
    },
});
