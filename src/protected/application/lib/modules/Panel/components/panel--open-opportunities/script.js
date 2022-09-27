app.component('panel--open-opportunities', {
    template: $TEMPLATES['panel--open-opportunities'],

    components: {
        carousel: Vue3Carousel.Carousel,
        slide: Vue3Carousel.Slide,
        pagination: Vue3Carousel.Pagination,
        navigation: Vue3Carousel.Navigation
    },

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('panel--open-opportunities')
        return { text }
    },

    props: {
    },

    data() {
        return {
            // query
            query: {
                '@order' : 'registrationFrom ASC',
                '@select' : 'id,name,singleUrl,files.avatar,shortDescription,registrationFrom,registrationTo',
                '@verified' : '1',
                'user' : `EQ(@me)`,
            },

            // carousel settings
            settings: {
                itemsToShow: 1,
                snapAlign: 'center',
            },

            // breakpoints are mobile first
            breakpoints: {
                1200: {
                    itemsToShow: 2.2,
                    snapAlign: "start"
                },
                1100: {
                    itemsToShow: 2,
                    snapAlign: "start"
                },
                1000: {
                    itemsToShow: 1.8,
                    snapAlign: "start"
                },
                900: {
                    itemsToShow: 1.6,
                    snapAlign: "start"
                },
                800: {
                    itemsToShow: 1.4,
                    snapAlign: "start"
                },
                700: {
                    itemsToShow: 1.7,
                    snapAlign: "start"
                },
                600: {
                    itemsToShow: 1.5,
                    snapAlign: "start"
                },
                500: {
                    itemsToShow: 1.2,
                    snapAlign: "start"
                },
            }
        }
    },

    computed: {
        getQuery() {
            this.query['registrationFrom'] = 'LTE('+this.futureDate()+')';
            this.query['registrationTo'] = 'GTE('+this.actualDate()+')';

            return this.query;
        }
    },
    
    methods: {
        formatDate(date, format) {
            var data = new Date(date);
            var dia = String(data.getDate()).padStart(2, '0');
            var mes = String(data.getMonth() + 1).padStart(2, '0');
            var ano = data.getFullYear();

            format = format.replace("dd", dia);
            format = format.replace("mm", mes);
            format = format.replace("yyyy", ano);
            return format;
        },

        formatTime(time, format) {
            var time = new Date(time);
            var hora = String(time.getHours());
            var minutos = String(time.getMinutes());
            var segundos = String(time.getSeconds());

            if (hora >=0 && hora <=9) hora = '0'+hora;
            if (minutos >=0 && minutos <=9) minutos = '0'+minutos;
            if (segundos >=0 && segundos <=9) segundos = '0'+segundos;
            
            format = format.replace("hh", hora);
            format = format.replace("mm", minutos);
            format = format.replace("ss", segundos);
            return format;
        },

        actualDate() {
            return this.formatDate(new Date(), 'yyyy-mm-dd');
        },

        futureDate() {
            var date = this.actualDate();
            var futureDate = new Date(date.replace(/\-/gi, ', '));
            futureDate.setMonth(futureDate.getMonth() + (1));
            return this.formatDate(futureDate, 'yyyy-mm-dd');
        }
    },
});
