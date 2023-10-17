
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
            arrow: null,
            status: [],
            data: $MAPAS.faq,
        }
    },
    methods: {
        toggle(index) {
            return this.status[index] = !this.status[index];
        },
        isOpen(index) {

            return this.status[index];
        },

    },
});
