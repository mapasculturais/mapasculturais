app.component('search-filter', {
    template: $TEMPLATES['search-filter'],

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('search-filter')
        return { text }
    },

    props: {
        position: {
            type: String,
            default: 'list'
        },
        pseudoQuery: {
            type: Object,
            required: true
        }
    },

    data() {
        return {
            show: false,
        }
    },

    computed: {
    },

    methods: {
        toggleFilter() {
            this.show = !this.show;
            const header = document.getElementById('main-header');
            if (this.show) {
                header.style.top=0;
                header.style.position = 'fixed';
            }
            else {
                header.removeAttribute('style');
                // header.style.top=unset;
                // header.style.position = 'relative';
            }
        }
    },
});
