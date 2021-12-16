app.component('modal', {
    data() {
        return {
            modalOpen: false,
            loading: {active: false}
        }
    },
    props: {
        title: String,
        closeButton: {
            type: Boolean,
            default: true
        }
    },
    methods: {
        open () {
            this.modalOpen = true;
        },
        close () {
            this.modalOpen = false;
        }
    },
    template: $TEMPLATES['modal']
});
