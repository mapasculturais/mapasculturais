app.component('panel--last-edited', {
    template: $TEMPLATES['panel--last-edited'],

    components: {
        carousel: Vue3Carousel.Carousel,
        slide: Vue3Carousel.Slide,
        pagination: Vue3Carousel.Pagination,
        navigation: Vue3Carousel.Navigation
    },

    props: {
        limit: {
            type: Number,
            default: 15
        }
    },

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('panel--last-edited')
        return { text }
    },

    async created() {
        const agentAPI = new API('agent');
        const spaceAPI = new API('space');
        const eventAPI = new API('event');
        const projectAPI = new API('project');
        const opportunityAPI = new API('opportunity');

        const query = this.query;
        query['@select'] = 'id,type,name,shortDescription,singleUrl,updateTimestamp,status';
        query['@order'] = 'updateTimestamp DESC';
        query['user'] = `EQ(@me)`;
        query['@permissions'] = 'view';
        query['status'] = 'GTE(0)';


        if (this.limit) {
            query['@limit'] = this.limit;
        }

        Promise.all([
            spaceAPI.find(query),
            agentAPI.find(query),
            eventAPI.find(query),
            projectAPI.find(query),
            opportunityAPI.find(query),
        ]).then(values => {
            this.spaces = values[0];
            this.agents = values[1];
            this.events = values[2];
            this.projects = values[3];
            this.opportunities = values[4];
            this.loading = false;
        })
    },

    data() {
        return {
            loading: true,
            query: {},
            agents: [],
            spaces: [],
            events: [],
            projects: [],
            opportunities: [],

            // carousel settings
            settings: {

                itemsToShow: 1.2,
                snapAlign: 'center',
            },
            breakpoints: {
                1300: {
                    itemsToScrool: 2.95,
                    itemsToShow: 2.3,
                    snapAlign: "start"
                },
                1200: {
                    itemsToScrool: 2.8,
                    itemsToShow: 2.2,
                    snapAlign: "start"
                },
                1100: {
                    itemsToScrool: 2.6,
                    itemsToShow: 2,
                    snapAlign: "start"
                },
                1000: {
                    itemsToShow: 1.8,
                    snapAlign: "start"
                },
                900: {
                    itemsToShow: 1.4,
                    snapAlign: "start"
                },
                800: {
                    itemsToShow: 1.2,
                    snapAlign: "start"
                },
                700: {
                    itemsToShow: 1.8,
                    snapAlign: "start"
                },
                600: {
                    itemsToScrool: 1.8,

                    itemsToShow: 1.9,
                    snapAlign: "start"
                },
                500: {
                    itemsToScrool: 1.5,

                    itemsToShow: 1.25,
                    snapAlign: "start"
                },
                400: {
                    itemsToScrool: 1.4,

                    itemsToShow: 1.2,
                    snapAlign: "start"
                },
                360: {
                    itemsToScrool: 1,

                    itemsToShow: 1.,
                    snapAlign: "start"
                },
                340: {
                    itemsToScrool: 1,

                    itemsToShow: 1.,
                    snapAlign: "start"
                },
            }
        }
    },

    computed: {
        entities() {
            const entities = [...this.projects, ...this.spaces, ...this.agents, ...this.opportunities, ...this.events];
            entities.sort((a, b) => {
                let dateA = a.updateTimestamp._date;
                let dateB = b.updateTimestamp._date;
                if(dateA < dateB) {
                    return 1;
                } else if(dateA > dateB) {
                    return -1;
                } else {
                    return 0;
                }
            });
            return entities.slice(0, this.limit);;
            
        }
    },
    methods: {
        resizeSlides() {
            this.$refs.carousel.updateSlideWidth();
        }
    },

});
