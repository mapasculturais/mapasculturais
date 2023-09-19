
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
        }
    },
    methods: {
        toggle() {
            this.status = this.status ? false : true;
        }
    },
});
