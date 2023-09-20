
app.component('faq-accordion', {
    template: $TEMPLATES['faq-accordion'],
    
    

    props: {
        name: {
            type: String,
            default: '',
        },
        
    },

    data() {
        return {
            status: false,
            data: $MAPAS.faq,
        }
    },
    methods: {
        toggle() {
            this.status = this.status ? false : true;
        },

    },
});
