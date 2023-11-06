app.component('faq-search-results', {
    template: $TEMPLATES['faq-search-results'],
   
    props: {
        index: {
            type: Boolean,
            default: false
        },
    },
    
    computed: {
        results() {
            const global = useGlobalState();
            return global.faqResults;

        }
    },
});
