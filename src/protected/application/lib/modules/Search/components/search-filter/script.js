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
        },
    },

    data() {
        
        return {
            showMenu: false, 
        }
    },

    computed: {
    },

    mounted() {
        window.addEventListener('mc-pin-click', this.closeFilter);

        if(this.position === 'list' && window.matchMedia('(min-width: 56.25rem)').matches) {
            this.showMenu = true;
        }
    },
    unmounted() {
        window.removeEventListener('mc-pin-click', this.closeFilter);
    },
    methods: {
        closeFilter(){
            this.showMenu = false;
            const header = document.getElementById('main-header');
            header.removeAttribute('style');
        },
        
        toggleFilter() {
            this.showMenu = !this.showMenu;
            const header = document.getElementById('main-header');
            if (!this.showMenu) {
                header.style.top=0;
            }
            else {
                header.removeAttribute('style');
                window.dispatchEvent(new CustomEvent('mc-map-filter-open', {detail:null}));
            }
        }
    },
});
