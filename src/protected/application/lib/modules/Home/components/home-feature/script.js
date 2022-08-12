app.component('home-feature', {
    template: $TEMPLATES['home-feature'],

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

    created(){

        this.spaceAPI = new API('space');
        this.agentAPI = new API('agent');
        this.findEntities();
    },
    
    data() {
        return{
            agents: [],
            spaces: [],

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
        entities() {
            const entities = this.spaces.concat(this.agents);
            return entities;
        }
    },
    
    methods: {
        async findEntities() {
            const query = {
                '@select': 'id,name,shortDescription,location,terms,seals,singleUrl',
            }
            this.spaces = await this.spaceAPI.find(query);
            this.agents = await this.agentAPI.find(query);
        }  
    },
});
