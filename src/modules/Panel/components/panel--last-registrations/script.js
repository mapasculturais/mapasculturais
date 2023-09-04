app.component('panel--last-registrations', {
    template: $TEMPLATES['panel--last-registrations'],

    components: {
        carousel: Vue3Carousel.Carousel,
        slide: Vue3Carousel.Slide,
        pagination: Vue3Carousel.Pagination,
        navigation: Vue3Carousel.Navigation
    },

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('panel--last-registrations')
        return { text }
    },

    async created(){

        const registrationAPI = new API('registration');
        
        const query = this.query;
        query['@select'] = 'id,number,opportunity.{name,files.avatar,registrationFrom,registrationTo}';
        query['@order'] = 'updateTimestamp DESC';
        query['@permissions'] = 'view';
        query['status'] = 'GTE(0)';
        query['user'] = `EQ(@me)`;

        if(this.limit) {
            query['@limit'] = this.limit;
        }

        this.registrations = await registrationAPI.find(query);
    },
    
    data() {
        return{
            query: {},
            registrations: [],

            // carousel settings
            settings: {
                itemsToShow: 1.2,
                snapAlign: 'center',
            },
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
                    itemsToShow: 2.4,
                    snapAlign: "start"
                },
                700: {
                    itemsToShow: 2.7,
                    snapAlign: "start"
                },
                600: {
                    itemsToShow: 2.5,
                    snapAlign: "start"
                },
                500: {
                    itemsToShow: 2.2,
                    snapAlign: "start"
                },
            }
        }
    },

    computed: {
        entities() {            
            if (this.registrations.metadata) {
                const entities = this.registrations
                return entities.filter(x => x.opportunity != undefined);
            } else {
                return {};
            }
        }
    },
    
    props: {
        limit: {
            type: Number,
            default: 15
        }
    },
});
