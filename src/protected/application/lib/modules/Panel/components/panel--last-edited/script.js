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

        const spaceAPI = new API('space');
        const projectAPI = new API('project');
        //const agentAPI = new API('agent');
        
        const query = this.query;

        query['@select'] = 'id,name,shortDescription,location,terms,seals,singleUrl,updateTimestamp,type';
        query['@order'] = 'updateTimestamp DESC';
        query['user'] = `EQ(@me)`;

        if(this.limit) {
            query['@limit'] = this.limit;
        }

        this.spaces = await spaceAPI.find(query);
        this.projects = await projectAPI.find(query);
    },
    
    data() {
        return{
            projects: [],
            spaces: [],

            // carousel settings
            settings: {
                itemsToShow: 2.2,
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
            const entities = this.spaces.concat(this.projects).sort((a,b) => {
                if (a.name > b.name) {
                    return 1;
                } else if (a.name == b.name) {
                    return 0
                } else {
                    return -1;
                }
            });
            return entities;
        }
    },
    
    props: {
        limit: {
            type: Number,
            default: 15
        },
        query: {
            type: Object,
            default: {}
        }
    },
    
    methods: {},
});
