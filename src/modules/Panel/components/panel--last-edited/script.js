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
            descriptionOverflow: {},
            descriptionRefs: {},
            descriptionObservers: {},
            resizeTimeout: null,
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

    watch: {
        entities: {
            handler() {
                // Apply the character-length heuristic immediately, before any geometric measurement
                const overflow = {};
                for (const entity of this.entities) {
                    overflow[entity.id] = (entity.shortDescription || '').trim().length > this.CHARACTER_OVERFLOW_LIMIT;
                }
                this.descriptionOverflow = { ...this.descriptionOverflow, ...overflow };
                this.$nextTick(() => {
                    this.checkDescriptionOverflow();
                });
            },
            deep: true
        }
    },

    mounted() {
        window.addEventListener('resize', this.debouncedCheckOverflow);
        this.$nextTick(() => {
            this.checkDescriptionOverflow();
        });
    },

    unmounted() {
        window.removeEventListener('resize', this.debouncedCheckOverflow);
        clearTimeout(this.resizeTimeout);
        for (const entityId in this.descriptionObservers) {
            this.descriptionObservers[entityId].disconnect();
        }
        this.descriptionObservers = {};
    },

    updated() {
        this.checkDescriptionOverflow();
    },

    methods: {
        setDescriptionRef(el, entityId) {
            if (this.descriptionObservers[entityId]) {
                this.descriptionObservers[entityId].disconnect();
                delete this.descriptionObservers[entityId];
            }

            if (el) {
                this.descriptionRefs[entityId] = el;

                if (window.ResizeObserver) {
                    const observer = new ResizeObserver(() => {
                        this.debouncedCheckOverflow();
                    });
                    observer.observe(el);
                    this.descriptionObservers[entityId] = observer;
                }
            } else {
                delete this.descriptionRefs[entityId];
            }
        },
        checkDescriptionOverflow() {
            this.$nextTick(() => {
                const overflow = {};
                for (const entityId in this.descriptionRefs) {
                    const el = this.descriptionRefs[entityId];
                    if (!el) {
                        continue;
                    }

                    // Reset clamp temporarily so we can compare the natural height.
                    // The force-measure class must be set on the wrapper, not on the <small> itself.
                    const wrapper = el.closest('.panel--last-edited__description');
                    wrapper?.classList.add('panel--last-edited__description--force-measure');
                    const naturalHeight = el.scrollHeight;
                    wrapper?.classList.remove('panel--last-edited__description--force-measure');

                    const renderedHeight = el.getBoundingClientRect().height;
                    const isOverflowing = naturalHeight > Math.ceil(renderedHeight + 1);

                    // Fallback: if the geometric check is inconclusive, use a character limit
                    const textLength = (this.descriptionRefs[entityId].textContent || '').trim().length;
                    const exceedsCharacterLimit = textLength > this.CHARACTER_OVERFLOW_LIMIT;

                    overflow[entityId] = isOverflowing || exceedsCharacterLimit;
                }
                this.descriptionOverflow = overflow;
            });
        },
        debouncedCheckOverflow() {
            clearTimeout(this.resizeTimeout);
            this.resizeTimeout = setTimeout(() => {
                this.checkDescriptionOverflow();
            }, 150);
        },
        toggleDescription(entityId) {
            this.expandedDescriptions[entityId] = !this.expandedDescriptions[entityId];
            this.$nextTick(() => {
                this.checkDescriptionOverflow();
                if (this.$refs.carousel) {
                    this.$refs.carousel.updateSlideWidth();
                    this.$refs.carousel.updateSlideHeight?.();
                    this.$refs.carousel.restartCarousel?.();
                }
                this.debouncedCheckOverflow();
            });
        },
        resizeSlides() {
            this.$refs.carousel.updateSlideWidth();
        }
    },

});
