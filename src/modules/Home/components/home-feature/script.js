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
        const text = Utils.getTexts('home-feature');
        const global = useGlobalState();
        return { text, global }
    },

    async created(){

        const spaceAPI = new API('space');
        const agentAPI = new API('agent');
        const projectAPI = new API('project');

        const query = this.query;

        query['@select'] = 'id,name,shortDescription,location,terms,seals,singleUrl';
        query['@order'] = this.order;

        if(this.limit) {
            query['@limit'] = this.limit;
        }

        let promises = [];

        if (this.global.enabledEntities.agents) {
            promises.push(agentAPI.find(query));
        }

        if (this.global.enabledEntities.spaces) {
            promises.push(spaceAPI.find(query));
        }

        if (this.global.enabledEntities.projects) {
            promises.push(projectAPI.find(query));
        }

        Promise.all(promises).then((values) => {
            this.agents = values[0] ?? [];
            this.spaces = values[1] ?? [];
            this.projects = values[2] ?? [];
        });
    },

    data() {
        return{
            agents: [],
            spaces: [],
            projects: [],

            // carousel settings
            settings: {
                itemsToShow: 1,
                snapAlign: 'center',
            },
            
            loading: true,

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
            const entities = [...this.spaces, ...this.agents, ...this.projects].sort((a,b) => {
                if (a.name > b.name) {
                    return 1;
                } else if (a.name == b.name) {
                    return 0
                } else {
                    return -1;
                }
            });
            this.loading = false;
            return entities;
        }
    },

    props: {
        limit: {
            type: Number,
            default: 15
        },
        order: {
            type: String,
            default: 'createTimestamp DESC'
        },
        query: {
            type: Object,
            default: {...$MAPAS.home.featured.filter}
        }
    },

    methods: {
        enabledEntities() {
            return this.global.enabledEntities.agents || this.global.enabledEntities.spaces || this.global.enabledEntities.projects;
        }
    },
});