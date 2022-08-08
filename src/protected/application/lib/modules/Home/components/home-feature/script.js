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

    props: {
        select: {
            type: String,
            default: 'id,name,shortDescription,terms,files,seals,classificacaoEtaria,terms'
        },
        query: {
            type: Object,
            default: {"@permissions": ""}
        },
        allBreakpoints: {
            type: Object
        }
    },

    data: () => ({
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
        },
    }),

    computed: {
    },
    
    methods: {
    },
});
