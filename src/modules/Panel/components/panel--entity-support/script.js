app.component('panel--entity-support', {
    template: $TEMPLATES['panel--entity-support'],

    components: {
        carousel: Vue3Carousel.Carousel,
        slide: Vue3Carousel.Slide,
        pagination: Vue3Carousel.Pagination,
        navigation: Vue3Carousel.Navigation
    },
    
    setup(props, { slots }) {
        const hasSlot = name => !!slots[name];
        // os textos estão localizados no arquivo texts.php deste componente 
        const text = Utils.getTexts('panel--entity-support')
        return { text, hasSlot }
    },
    data() {
        return {
            opportunities: $MAPAS.entitySupports,

            // carousel settings
            settings: {
                itemsToShow: 1,
                snapAlign: 'start',
            },
        };
    },

    computed: {
        isAdmin() {
            return Object.values($MAPAS.currentUserRoles).includes('admin');
        }
    },
});
