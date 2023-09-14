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

    async created(){
        const opportunityAPI = new API('opportunity');
        
        const query = this.query;
        query['@select'] = 'id,name,singleUrl,files.avatar,shortDescription,registrationFrom,registrationTo';
        query['@order'] = 'registrationFrom DESC';
        query['@verified'] = '1';
        query['user'] = `EQ(@me)`;
        query['registrationFrom'] = 'LTE('+this.futureDate()+')';
        query['registrationTo'] = 'GTE('+this.actualDate()+')';

        if(this.limit) {
            query['@limit'] = this.limit;
        }

        this.opportunities = await opportunityAPI.find(query);
    },

    props: {
        limit: {
            type: Number,
            default: 0
        }
    },

    data() {
        return {
            query: {},
            opportunities: [],

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
        entities() {
            if (this.opportunities.metadata) {
                const entities = this.opportunities;                
                entities.sort((a,b) => {
                    let dateA = a.updateTimestamp?.date('sql');
                    let dateB = b.updateTimestamp?.date('sql');
                    return (dateA?.localeCompare(dateB));                    
                });
                return entities.slice(0, this.limit);;
            } else {
                return {};
            }
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
            const futureDate = new Date();
            futureDate.setMonth(futureDate.getMonth() + 1);
            return this.formatDate(futureDate, 'yyyy-mm-dd');
        }
    },
});
