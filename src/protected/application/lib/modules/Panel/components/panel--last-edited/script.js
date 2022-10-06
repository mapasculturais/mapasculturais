app.component('panel--last-edited', {
    template: $TEMPLATES['panel--last-edited'],

    components: {
        carousel: Vue3Carousel.Carousel,
        slide: Vue3Carousel.Slide,
        pagination: Vue3Carousel.Pagination,
        navigation: Vue3Carousel.Navigation
    },

    setup() { 
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('panel--last-edited')
        return { text }
    },

    async created(){
        const agentAPI = new API('agent');
        const spaceAPI = new API('space');
        const eventAPI = new API('event');
        const projectAPI = new API('project');
        const opportunityAPI = new API('opportunity');
        
        const query = this.query;
        query['@select'] = 'id,name,shortDescription,singleUrl,updateTimestamp,type';
        query['@order'] = 'updateTimestamp DESC';
        query['user'] = `EQ(@me)`;

        if(this.limit) {
            query['@limit'] = this.limit;
        }

        this.spaces = await spaceAPI.find(query);
        this.agents = await agentAPI.find(query);
        this.events = await eventAPI.find(query);
        this.projects = await projectAPI.find(query);
        this.opportunities = await opportunityAPI.find(query);
    },
    
    data() {
        return{
            query: {},
            agents: [],
            spaces: [],
            events: [],
            projects: [],
            opportunities: [],

            // carousel settings
            settings: {
                itemsToShow: 2.2,
                snapAlign: 'center',
            },
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
            if (this.projects.metadata && this.spaces.metadata && this.agents.metadata && this.opportunities.metadata && this.events.metadata) {
                const entities = this.projects.concat(this.spaces, this.agents, this.opportunities, this.events);                
                entities.sort((a,b) => {
                    let dateA = a.updateTimestamp.date('sql');
                    let dateB = b.updateTimestamp.date('sql');

                    return (dateA.localeCompare(dateB));                    
                });
                return entities.slice(0, this.limit);;
            } else {
                return {};
            }
        }
    },
    
    props: {
        limit: {
            type: Number,
            default: 5
        }
    },
});
