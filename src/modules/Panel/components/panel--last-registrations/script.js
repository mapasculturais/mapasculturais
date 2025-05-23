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
        query['@select'] = 'id,number,opportunity.{name,files.avatar,registrationFrom,registrationTo},owner.{name},category,range,proponentType,agentRelations,createTimestamp';
        query['@order'] = 'updateTimestamp ASC';
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
                    itemsToScrool: 2.8,
                    itemsToShow: 2.2,
                    snapAlign: "start"
                },
                900: {
                    itemsToShow: 1.6,
                    snapAlign: "start"
                },
                400: {
                    itemsToScrool: 1.25,
                    itemsToShow: 1.15,
                    snapAlign: "center"
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
