app.component('home-opportunities', {
    template: $TEMPLATES['home-opportunities'],

    components: {
        carousel: Vue3Carousel.Carousel,
        slide: Vue3Carousel.Slide,
        pagination: Vue3Carousel.Pagination,
        navigation: Vue3Carousel.Navigation
    },

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('home-opportunities')
        return { text }
    },

    created() { 
        /* window.onload = function () {
            
            this.lista = document.querySelector('.home-opportunities__content--cards-list');
            this.btnLeft = document.querySelector('.actions').firstElementChild;
            this.btnRight = document.querySelector('.actions').lastElementChild;
            this.scrollMin = 0;

            // card = 328;
            // gap/lastGap = 40;
            // scrollMax = (((card+gap) * (totalElementos - 3)) - lastGap); 
            this.scrollMax = (this.lista.childElementCount > 3) ? ((368 * (this.lista.childElementCount - 3)) - 40) : ((368 * this.lista.childElementCount) - 40); 

            if ((368 * this.lista.childElementCount) >= this.lista.offsetWidth) {
                // desabilida apenas o left
                this.btnLeft.disabled = true;
            } else {
                // desabilida ambos
                this.btnLeft.disabled = true;
                this.btnRight.disabled = true;
            }

            console.log(this.lista);
        }*/
    }, 

    props: {
        select: {
            type: String,
            default: 'id,name,shortDescription,terms,seals'
        },
        query: {
            type: Object,
            default: {"@permissions": ""}
        },
        allBreakpoints: {
            type: Object
        }
    },

    data: () => ({
        // carousel settings
        settings: {
            itemsToShow: 1,
            snapAlign: 'center',
        },
        // breakpoints are mobile first
        breakpoints: {
            1200: {
                itemsToShow: 3.2,
                snapAlign: "start"
            },
            1100: {
                itemsToShow: 3,
                snapAlign: "start"
            },
            1000: {
                itemsToShow: 2.8,
                snapAlign: "start"
            },
            900: {
                itemsToShow: 2.6,
                snapAlign: "start"
            },
            800: {
                itemsToShow: 2.2,
                snapAlign: "start"
            },
            700: {
                itemsToShow: 2,
                snapAlign: "start"
            },
            600: {
                itemsToShow: 1.5,
                snapAlign: "start"
            },
            500: {
                itemsToShow: 1,
                snapAlign: "start"
            },
        },
    }),

    /* data() {
        return {
            leftDisabled: false,
            rightDisabled: false,
            scroll: 0,
            lista: '',
            btnLeft: '',
            btnRight: '',
            scrollMax: 0,
            scrollMin: 0
        }
    }, */

    computed: {
    },
    
    methods: {
        /* right() {    
            console.log(this.lista);
            if ((368 * this.lista.childElementCount) >= this.lista.offsetWidth) {       
                if (this.btnLeft.disabled == true) this.btnLeft.disabled = false;
                
                if (scroll >= scrollMax) {
                    this.scroll = this.scrollMax;
                    this.btnRight.disabled = true;
                } else {
                    this.scroll = scroll + 368;
                    this.btnRight.disabled = false;
                }

                this.lista.scroll({ left: scroll, behavior: 'smooth' });
            }
        },

        left() {
            console.log(this.lista);
            if ((368 * this.lista.childElementCount) >= this.lista.offsetWidth) {
                if (btnRight.disabled == true) btnRight.disabled = false;

                if (scroll <= scrollMin) {
                    this.scroll = this.scrollMin
                    this.btnLeft.disabled = true;
                } else {
                    this.scroll = scroll - 368;
                    this.btnLeft.disabled = false;
                }

                this.lista.scroll({ left: scroll, behavior: 'smooth' });
            }
        } */

    },
});
