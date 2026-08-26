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
        const global = useGlobalState();
        return { text, global }
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
            this.spaces = this.global.enabledEntities.spaces ? values[0] : [];
            this.agents = this.global.enabledEntities.agents ? values[1] : [];
            this.events = this.global.enabledEntities.events ? values[2] : [];
            this.projects = this.global.enabledEntities.projects ? values[3] : [];
            this.opportunities = this.global.enabledEntities.opportunities ? values[4] : [];
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
            expandedDescriptions: {},
            // Same heuristic used by the home entity-card: descriptions longer than this get the toggle button
            CHARACTER_OVERFLOW_LIMIT: 120,

            // carousel settings
            settings: {
                itemsToScroll: 1,
                itemsToShow: 1,
                snapAlign: 'center',
            },
            breakpoints: {
                700: {
                    itemsToScroll: 1,
                    itemsToShow: 2,
                    snapAlign: "start"
                },
                1200: {
                    itemsToScroll: 1,
                    itemsToShow: 3,
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
            return entities.slice(0, this.limit);
            
        }
    },

    methods: {
        shouldShowMoreButton(entity) {
            return (entity.shortDescription || '').trim().length > this.CHARACTER_OVERFLOW_LIMIT;
        },
        descriptionKey(entity) {
            return `${entity.__objectType}-${entity.id}`;
        },
        toggleDescription(entity) {
            const key = this.descriptionKey(entity);
            this.expandedDescriptions[key] = !this.expandedDescriptions[key];
            this.$nextTick(() => {
                if (this.$refs.carousel) {
                    this.$refs.carousel.updateSlideWidth();
                    this.$refs.carousel.updateSlideHeight?.();
                    this.$refs.carousel.restartCarousel?.();
                }
            });
        },
        resizeSlides() {
            this.$refs.carousel.updateSlideWidth();
        }
    },

});
