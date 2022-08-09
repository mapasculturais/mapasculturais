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

    props: {
    },

    data() {
        return {
            // query
            query: {
                '@order' : 'registrationFrom ASC',
                '@select' : 'id,name,shortDescription,terms,seals',
                '@verified' : '1'
            },

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
            }
        }
    },

    computed: {
        getQuery() {
            this.query['registrationFrom'] = 'LTE('+this.futureDate()+')';
            this.query['registrationTo'] = 'GTE('+this.actualDate()+')';
            

            console.log(this.query);

            return this.query;
        }
    },
    
    methods: {
        actualDate() {
            var data = new Date();
            var dia = String(data.getDate()).padStart(2, '0');
            var mes = String(data.getMonth() + 1).padStart(2, '0');
            var ano = data.getFullYear();

            return (ano + '-' + mes + '-' + dia);
        },

        futureDate() {
            var date = this.actualDate();
            var futureDate = new Date(date.replace(/\-/gi, ', '));
            futureDate.setMonth(futureDate.getMonth() + (1));

            var dia = String(futureDate.getDate()).padStart(2, '0');
            var mes = String(futureDate.getMonth() + 1).padStart(2, '0');
            var ano = futureDate.getFullYear();

            return (ano + '-' + mes + '-' + dia);
        }
    },
});
