app.component('panel--pending-evaluations', {
    template: $TEMPLATES['panel--pending-evaluations'],

    components: {
        carousel: Vue3Carousel.Carousel,
        slide: Vue3Carousel.Slide,
        pagination: Vue3Carousel.Pagination,
        navigation: Vue3Carousel.Navigation
    },

    setup() {
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('panel--pending-evaluations');
        const entities = [];
        const api = new API("opportunity");
        
        
        for(let raw of $MAPAS.opportunitiesCanBeEvaluated){
           const opportunity = api.getEntityInstance(raw.id);
           opportunity.populate(raw);
           entities.push(opportunity);
        }
        
        return { text, entities }
    },

    created() {
        this.loading = false;
    },
 
    data() {
        return {
            loading: true,
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
    methods: {
        evaluationFrom(entity) {

            const evalFrom = entity.evaluationMethodConfiguration.evaluationFrom;
            return evalFrom
         },
         evaluationTo(entity) {

            const evalTo = entity.evaluationMethodConfiguration.evaluationTo;
            return  evalTo
         },
        resizeSlides() {
            this.$refs.carousel.updateSlideWidth();
        },
        ownerType(owner) {
            switch (owner.__objectType) {
                case 'agent':
                    return this.text('agente');
                case 'space':
                    return this.text('espaço');
                case 'event':
                    return this.text('evento');
                case 'opportunity':
                    return this.text('opportunidade');
                case 'project':
                    return this.text('projeto');
            }
        }
    },

});
